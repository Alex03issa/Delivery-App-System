<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CryptoController extends Controller
{
    public function checkout(Payment $payment)
    {
        $response = Http::withHeaders([
            'X-CC-Api-Key' => env('COINBASE_COMMERCE_KEY'),
            'X-CC-Version' => '2018-03-22',
        ])->post('https://api.commerce.coinbase.com/charges', [
            'name' => 'Delivery Payment',
            'description' => 'Payment for Delivery #' . $payment->delivery_id,
            'local_price' => [
                'amount' => $payment->amount,
                'currency' => $payment->currency->code ?? 'USD',
            ],
            'pricing_type' => 'fixed_price',
            'metadata' => [
                'payment_id' => $payment->id,
            ],
            'redirect_url' => route('payment.success', $payment->id),
            'cancel_url' => route('payment.form', $payment->delivery_id),
        ]);
        \Log::info('Coinbase API key used:', ['key' => config('services.coinbase.key')]);


        \Log::info('Coinbase response:', $response->json());

        \Log::error('Coinbase error:', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->successful()) {
            return redirect($response->json('data.hosted_url'));
        }

        return back()->with('error', 'Unable to initiate crypto payment.');
    }

    public function webhook(Request $request)
    {

        \Log::info('COINBASE WEBHOOK HIT');
        \Log::info($request->getContent());

        $payload = $request->getContent();
        $signature = $request->header('X-CC-Webhook-Signature');
        $sharedSecret = env('COINBASE_WEBHOOK_SECRET');

        // Step 1: Verify signature
        $computedSignature = hash_hmac('sha256', $payload, $sharedSecret);
        if (!hash_equals($computedSignature, $signature)) {
            return response('Invalid signature', 400);
        }

        // Step 2: Decode JSON
        $event = json_decode($payload);

        // Step 3: Handle charge status
        if (isset($event->event->type) && $event->event->type === 'charge:confirmed') {
            $paymentId = $event->event->data->metadata->payment_id ?? null;

            if ($paymentId) {
                $payment = Payment::find($paymentId);
                if ($payment && $payment->payment_status !== 'confirmed') {
                    $payment->update([
                        'payment_status' => 'confirmed',
                        'paid_at' => now(),
                        'transaction_id' => $event->event->data->code ?? null,
                    ]);
                }
            }
        }

        return response('Webhook handled', 200);
    }

}
