<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function checkout(Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->payment_status === 'confirmed') {
            return redirect()->route('delivery.track', $payment->delivery_id)
                ->with('success', 'This delivery is already paid.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($payment->currency->code),
                    'unit_amount' => $this->amountInCents($payment),
                    'product_data' => [
                        'name' => 'Delivery Payment #' . $payment->delivery_id,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'client_reference_id' => (string) $payment->id,
            'metadata' => ['payment_id' => (string) $payment->id],
            // Stripe replaces {CHECKOUT_SESSION_ID}; it must not be URL-encoded.
            'success_url' => route('payment.success', $payment->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.form', $payment->delivery_id),
        ]);

        $payment->update(['transaction_id' => $session->id]);

        return redirect($session->url);
    }

    /**
     * Stripe redirects here after checkout. The payment is only marked as
     * paid after the Checkout Session is fetched from Stripe and is paid.
     */
    public function success(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        if ($payment->payment_status === 'confirmed') {
            return redirect()->route('delivery.track', $payment->delivery_id)
                ->with('success', 'Payment successful and confirmed.');
        }

        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '' || $sessionId !== $payment->transaction_id) {
            return redirect()->route('payment.form', $payment->delivery_id)
                ->with('error', 'We could not verify this payment.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::warning('Stripe session lookup failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);

            return redirect()->route('payment.form', $payment->delivery_id)
                ->with('error', 'We could not verify this payment.');
        }

        $paid = $session->payment_status === 'paid'
            && (string) $session->client_reference_id === (string) $payment->id
            && (int) $session->amount_total === $this->amountInCents($payment);

        if (!$paid) {
            return redirect()->route('payment.form', $payment->delivery_id)
                ->with('error', 'The payment has not been completed.');
        }

        $payment->markConfirmed($session->id);

        return redirect()->route('delivery.track', $payment->delivery_id)
            ->with('success', 'Payment successful and confirmed.');
    }

    private function amountInCents(Payment $payment): int
    {
        return (int) round((float) $payment->amount * 100);
    }

    private function authorizePayment(Payment $payment): void
    {
        abort_unless($payment->delivery && $payment->delivery->isOwnedByClient(auth()->user()), 403);
    }
}
