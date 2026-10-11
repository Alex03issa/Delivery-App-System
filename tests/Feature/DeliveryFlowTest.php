<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Client;
use App\Models\DeliveryRequest;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DeliveryFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hash_secret' => 'test-secret']);
        $this->seed(CurrencySeeder::class);
    }

    /** Create a verified user the same way signupController stores it. */
    private function makeUser(string $role, string $email): User
    {
        $email = strtolower($email);
        $hash = hash_hmac('sha256', $email, config('app.hash_secret'));

        $user = User::create([
            'name' => ucfirst($role) . ' ' . substr(md5($email), 0, 4),
            'email' => Crypt::encryptString(json_encode([
                'email' => Crypt::encryptString($email),
                'hash' => $hash,
            ])),
            'hashed_email' => $hash,
            'phone' => '70000000',
            'password' => Hash::make('Secret#123'),
            'role' => $role,
            'is_verified' => true,
        ]);

        Client::create(['user_id' => $user->id]);

        if ($role === 'driver') {
            Driver::create([
                'user_id' => $user->id,
                'vehicle_type' => 'scooter',
                'plate_number' => 'B 12345',
                'license_number' => 'L-1',
                'pricing_type' => 'fixed',
            ]);
        }

        return $user->fresh();
    }

    private function createDelivery(User $client): DeliveryRequest
    {
        $this->actingAs($client)->post('/delivery/store', [
            'pickup_location' => 'Hamra, Beirut',
            'pickup_lat' => 33.8959,
            'pickup_lng' => 35.4797,
            'dropoff_location' => 'Achrafieh, Beirut',
            'dropoff_lat' => 33.8869,
            'dropoff_lng' => 35.5131,
            'length_cm' => 20, 'width_cm' => 10, 'height_cm' => 5,
            'package_weight' => 1.5,
            'package_size' => 'small',
            'urgency_level' => 'normal',
            'delivery_date' => now()->addDay()->toDateTimeString(),
            'payment_method' => 'cod',
            'price' => 10,
            'extra_charge' => 2,
            'contact_phone' => '+961 70 123 456',
        ])->assertRedirect();

        return DeliveryRequest::latest('id')->firstOrFail();
    }

    public function test_guests_and_wrong_roles_are_kept_out(): void
    {
        $this->get('/chat')->assertRedirect('/login');
        $this->get('/driver/dashboard')->assertRedirect('/login');
        $this->get('/client/dashboard')->assertRedirect('/login');
        $this->get('/driver/details')->assertRedirect('/login');

        $client = $this->makeUser('client', 'c@example.com');
        $this->actingAs($client)->get('/admin')->assertForbidden();
        $this->actingAs($client)->get('/driver/dashboard')->assertForbidden();

        // Webhook is CSRF-exempt (would be 419 otherwise) and refuses unsigned calls.
        $this->post('/webhook/coinbase', [])->assertStatus(503);
    }

    public function test_full_delivery_flow_with_live_tracking(): void
    {
        $client = $this->makeUser('client', 'client@example.com');
        $driver = $this->makeUser('driver', 'driver@example.com');
        $stranger = $this->makeUser('client', 'other@example.com');

        $delivery = $this->createDelivery($client);
        $this->assertSame('+961 70 123 456', $delivery->dropoff_contact_phone);
        $this->assertSame('pending', $delivery->status);

        // Only the owner can see it
        $this->actingAs($client)->get("/delivery/track/{$delivery->id}")->assertOk()->assertSee('Pay for this delivery');
        $this->actingAs($stranger)->get("/delivery/track/{$delivery->id}")->assertForbidden();

        // Driver accepts and starts
        $this->actingAs($driver)->post("/driver/accept/{$delivery->id}")->assertRedirect('/driver/deliveries');
        $this->actingAs($driver)->post("/driver/deliveries/{$delivery->id}/start")->assertRedirect();
        $this->assertSame('in_progress', $delivery->fresh()->status);

        // Started delivery stays in the driver's list
        $this->actingAs($driver)->get('/driver/deliveries')->assertOk()->assertSee('Achrafieh');

        // Driver pushes GPS, client reads it
        $this->actingAs($driver)->postJson('/api/tracking/update-location', [
            'delivery_id' => $delivery->id, 'lat' => 33.89, 'lng' => 35.50,
        ])->assertOk();

        $this->actingAs($client)->getJson("/api/tracking/driver-location/{$driver->id}")
            ->assertOk()->assertJson(['lat' => '33.8900000', 'lng' => '35.5000000']);
        $this->actingAs($stranger)->getJson("/api/tracking/driver-location/{$driver->id}")->assertForbidden();

        // Sanctum-free driver endpoint also works with the session
        $this->actingAs($driver)->postJson('/api/driver/update-location', ['lat' => 33.9, 'lng' => 35.51])->assertOk();

        // Complete
        $this->actingAs($driver)->get("/delivery/track/{$delivery->id}")->assertOk()->assertSee('Mark as Delivered');
        $this->actingAs($driver)->post("/driver/delivery/{$delivery->id}/complete")->assertRedirect('/driver/deliveries');
        $delivery->refresh();
        $this->assertSame('delivered', $delivery->status);
        $this->assertNotNull($delivery->completed_at);

        // Dashboards render real data
        $this->actingAs($client)->get('/client/dashboard')->assertOk()->assertSee($delivery->tracking_code);
        $this->actingAs($driver)->get('/driver/dashboard')->assertOk()->assertSee('Delivered');
        $admin = $this->makeUser('admin', 'admin@example.com');
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee($delivery->tracking_code);
    }

    public function test_payment_amounts_are_computed_on_the_server(): void
    {
        $client = $this->makeUser('client', 'payer@example.com');
        $stranger = $this->makeUser('client', 'nosy@example.com');
        $delivery = $this->createDelivery($client);
        $eur = \App\Models\Currency::where('code', 'EUR')->first();

        $this->actingAs($stranger)->get("/payment/form/{$delivery->id}")->assertForbidden();
        $this->actingAs($client)->get("/payment/form/{$delivery->id}")->assertOk();

        $this->actingAs($client)->post('/payment/store', [
            'delivery_id' => $delivery->id,
            'payment_method' => 'cod',
            'currency_id' => $eur->id,
            'amount' => 0.01,          // tampered values are ignored
            'platform_fee' => 0,
            'driver_share' => 999,
        ])->assertRedirect("/delivery/track/{$delivery->id}");

        $payment = Payment::where('delivery_id', $delivery->id)->firstOrFail();
        $this->assertEqualsWithDelta(12 / 1.1, (float) $payment->amount, 0.0001);
        $this->assertEquals(1.20, (float) $payment->platform_fee);
        $this->assertEquals(10.80, (float) $payment->driver_share);
    }

    public function test_stripe_success_url_cannot_mark_a_payment_paid(): void
    {
        $client = $this->makeUser('client', 'card@example.com');
        $delivery = $this->createDelivery($client);
        $usd = \App\Models\Currency::where('code', 'USD')->first();

        $payment = Payment::create([
            'delivery_id' => $delivery->id,
            'payment_method' => 'card',
            'currency_id' => $usd->id,
            'amount' => 12,
            'payment_status' => 'pending',
            'transaction_id' => 'cs_test_real',
        ]);

        auth()->logout();
        $this->get("/payment/success/{$payment->id}")->assertRedirect('/login');
        $this->actingAs($client)->get("/payment/success/{$payment->id}")->assertRedirect();
        $this->actingAs($client)->get("/payment/success/{$payment->id}?session_id=cs_test_fake")->assertRedirect();

        $this->assertSame('pending', $payment->fresh()->payment_status);
    }

    public function test_chat_is_limited_to_delivery_partners(): void
    {
        $client = $this->makeUser('client', 'chatc@example.com');
        $driver = $this->makeUser('driver', 'chatd@example.com');
        $stranger = $this->makeUser('client', 'chats@example.com');

        $delivery = $this->createDelivery($client);
        $this->actingAs($driver)->post("/driver/accept/{$delivery->id}");

        $this->actingAs($client)->get('/chat')->assertOk()->assertSee($driver->name);
        $this->actingAs($driver)->get('/chat')->assertOk()->assertSee($client->name);

        $this->actingAs($client)->post('/send-message', ['user' => $driver->id, 'message' => 'Hi <b>there</b>'])->assertSuccessful();
        $this->assertSame('Hi &lt;b&gt;there&lt;/b&gt;', Chat::first()->message);

        $this->actingAs($client)->post('/send-message', ['user' => $stranger->id, 'message' => 'spam'])->assertForbidden();
        $this->actingAs($driver)->getJson("/communication-history?userID={$client->id}")->assertOk()->assertJsonCount(1);
    }

    public function test_login_requires_otp_even_with_remember_me(): void
    {
        Mail::fake();
        $user = $this->makeUser('client', 'otp@example.com');

        // Mixed-case email and "remember" still lead to the OTP step
        $this->post('/login', ['email' => 'OTP@Example.com', 'password' => 'Secret#123', 'remember' => 'on'])
            ->assertRedirect('/otp/verify');
        $this->assertGuest();
        $this->assertFalse((bool) $user->fresh()->skip_otp_once);

        // A second login attempt cannot skip the OTP
        $this->post('/login', ['email' => 'otp@example.com', 'password' => 'Secret#123'])
            ->assertRedirect('/otp/verify');
        $this->assertGuest();

        $code = json_decode($user->fresh()->otp_code, true)['code'];
        $this->post('/otp/verify', ['otp' => $code])->assertRedirect('/client/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_nowpayments_ipn_confirms_only_signed_callbacks(): void
    {
        config(['services.nowpayments.ipn_secret' => 'ipn-secret']);
        $client = $this->makeUser('client', 'crypto@example.com');
        $delivery = $this->createDelivery($client);
        $btc = \App\Models\Currency::where('code', 'BTC')->first();
        $payment = Payment::create([
            'delivery_id' => $delivery->id, 'payment_method' => 'crypto',
            'currency_id' => $btc->id, 'amount' => 0.0004, 'payment_status' => 'pending',
        ]);

        $body = ['payment_status' => 'finished', 'order_id' => (string) $payment->id, 'payment_id' => 555];
        $this->postJson('/webhook/nowpayments', $body, ['x-nowpayments-sig' => 'forged'])->assertStatus(400);
        $this->assertSame('pending', $payment->fresh()->payment_status);

        ksort($body);
        $sig = hash_hmac('sha512', json_encode($body, JSON_UNESCAPED_SLASHES), 'ipn-secret');
        $this->postJson('/webhook/nowpayments', $body, ['x-nowpayments-sig' => $sig])->assertOk();
        $this->assertSame('confirmed', $payment->fresh()->payment_status);
        $this->assertTrue($delivery->fresh()->is_paid);
    }
}
