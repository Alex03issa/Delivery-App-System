<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoController extends Controller
{
    /**
     * Coinbase Commerce checkout (alternative crypto gateway; the payment form
     * uses NOWPayments by default).
     */
    public function checkout(Payment $payment)
    {
        $this->authorizePayment($payment);

        $response = Http::withHeaders([
            'X-CC-Api-Key' => config('services.coinbase.key'),
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
            'redirect_url' => route('payment.return', $payment->id),
            'cancel_url' => route('payment.form', $payment->delivery_id),
        ]);

        if ($response->successful()) {
            return redirect($response->json('data.hosted_url'));
        }

        Log::warning('Coinbase charge creation failed', [
            'payment_id' => $payment->id,
            'status' => $response->status(),
        ]);

        return back()->with('error', 'Unable to initiate crypto payment.');
    }

    /**
     * Coinbase Commerce webhook: the only place a Coinbase payment is confirmed.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('X-CC-Webhook-Signature', '');
        $sharedSecret = (string) config('services.coinbase.webhook_secret');

        if ($sharedSecret === '') {
            return response('Webhook not configured', 503);
        }

        $computedSignature = hash_hmac('sha256', $payload, $sharedSecret);
        if (!hash_equals($computedSignature, $signature)) {
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload);

        if (isset($event->event->type) && $event->event->type === 'charge:confirmed') {
            $paymentId = $event->event->data->metadata->payment_id ?? null;

            if ($paymentId && ($payment = Payment::find($paymentId))) {
                $payment->markConfirmed($event->event->data->code ?? null);
            }
        }

        return response('Webhook handled', 200);
    }

    /**
     * NOWPayments hosted invoice. The client picks the crypto coin on the
     * payment form; the price is always expressed in USD (the delivery price).
     */
    public function nowPaymentsCheckout(Payment $payment)
    {
        $this->authorizePayment($payment);

        $usdAmount = round((float) $payment->amount * (float) $payment->conversion_rate, 2);

        $response = Http::withHeaders([
            'x-api-key' => config('services.nowpayments.key'),
        ])->post('https://api.nowpayments.io/v1/invoice', [
            'price_amount' => $usdAmount,
            'price_currency' => 'usd',
            'pay_currency' => strtolower($payment->currency->code),
            'order_id' => (string) $payment->id,
            'order_description' => "Payment for Delivery #{$payment->delivery_id}",
            'ipn_callback_url' => route('webhook.nowpayments'),
            'success_url' => route('payment.return', $payment->id),
            'cancel_url' => route('payment.form', $payment->delivery_id),
        ]);

        if ($response->successful() && isset($response['invoice_url'])) {
            return redirect($response['invoice_url']);
        }

        Log::warning('NOWPayments invoice creation failed', [
            'payment_id' => $payment->id,
            'status' => $response->status(),
        ]);

        return back()->with('error', 'Unable to start NOWPayments transaction.');
    }

    /**
     * NOWPayments IPN callback. Signature = HMAC-SHA512 of the JSON body with
     * keys sorted, using the IPN secret from the NOWPayments dashboard.
     */
    public function nowPaymentsWebhook(Request $request)
    {
        $secret = (string) config('services.nowpayments.ipn_secret');
        if ($secret === '') {
            return response('Webhook not configured', 503);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return response('Invalid payload', 400);
        }

        $sorted = $this->ksortRecursive($data);
        $expected = hash_hmac('sha512', json_encode($sorted, JSON_UNESCAPED_SLASHES), $secret);
        $signature = (string) $request->header('x-nowpayments-sig', '');

        if (!hash_equals($expected, $signature)) {
            return response('Invalid signature', 400);
        }

        if (in_array($data['payment_status'] ?? null, ['finished', 'confirmed'], true)
            && ($payment = Payment::find($data['order_id'] ?? null))) {
            $payment->markConfirmed(isset($data['payment_id']) ? (string) $data['payment_id'] : null);
        }

        return response('OK', 200);
    }

    private function ksortRecursive(array $data): array
    {
        ksort($data);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->ksortRecursive($value);
            }
        }

        return $data;
    }

    private function authorizePayment(Payment $payment): void
    {
        abort_unless($payment->delivery && $payment->delivery->isOwnedByClient(auth()->user()), 403);
        abort_if($payment->payment_status === 'confirmed', 409, 'This payment is already confirmed.');
    }
}
