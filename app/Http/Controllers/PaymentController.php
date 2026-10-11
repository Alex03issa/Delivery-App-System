<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\DeliveryRequest;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private const CRYPTO_CODES = ['BTC', 'ETH', 'USDT', 'USDC'];

    public function showPaymentForm(DeliveryRequest $delivery)
    {
        $this->authorizeDelivery($delivery);

        if ($delivery->payment && $delivery->payment->payment_status === 'confirmed') {
            return redirect()->route('delivery.track', $delivery->id)
                ->with('success', 'This delivery is already paid.');
        }

        $breakdown = $this->breakdown($delivery);

        return view('payments.form', [
            'delivery' => $delivery,
            'currencies' => Currency::all(),
            'baseAmount' => $breakdown['base'],
            'convertedAmount' => null,
            'platformFee' => $breakdown['platform_fee'],
            'driverShare' => $breakdown['driver_share'],
            'centerShare' => $breakdown['center_share'],
            'hasCenterShare' => $breakdown['center_share'] > 0,
            'defaultCurrency' => Currency::where('code', 'USD')->first(),
            'title' => 'Payment Page',
        ]);
    }

    /**
     * Create the payment. Only the delivery, method and currency come from
     * the form; every amount is computed here from the delivery price.
     */
    public function store(Request $request)
    {
        $request->validate([
            'delivery_id' => 'required|exists:delivery_requests,id',
            'payment_method' => 'required|in:card,crypto,cod',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $delivery = DeliveryRequest::findOrFail($request->delivery_id);
        $this->authorizeDelivery($delivery);

        if ($delivery->payment && $delivery->payment->payment_status === 'confirmed') {
            return redirect()->route('delivery.track', $delivery->id)
                ->with('success', 'This delivery is already paid.');
        }

        $currency = Currency::findOrFail($request->currency_id);
        $isCrypto = in_array(strtoupper($currency->code), self::CRYPTO_CODES, true);

        if ($request->payment_method === 'crypto' && !$isCrypto) {
            return back()->with('error', 'Please choose a cryptocurrency for crypto payments.');
        }
        if ($request->payment_method !== 'crypto' && $isCrypto) {
            return back()->with('error', 'Please choose a regular currency for this payment method.');
        }

        $rate = (float) $currency->rate_to_usd; // USD value of 1 unit of the currency
        if ($rate <= 0) {
            return back()->with('error', 'This currency is not available right now.');
        }

        $breakdown = $this->breakdown($delivery);
        $converted = round($breakdown['base'] / $rate, 10);
        $isCod = $request->payment_method === 'cod';

        try {
            $payment = Payment::updateOrCreate(
                ['delivery_id' => $delivery->id],
                [
                    'payment_method'   => $request->payment_method,
                    'currency_id'      => $currency->id,
                    'amount'           => $converted,
                    'converted_amount' => $converted,
                    'conversion_rate'  => $rate,
                    'platform_fee'     => $breakdown['platform_fee'],
                    'driver_share'     => $breakdown['driver_share'],
                    'center_share'     => $breakdown['center_share'],
                    // Cash is collected by the driver; the delivery is flagged
                    // as paid when the driver marks it delivered.
                    'payment_status'   => $isCod ? 'confirmed' : 'pending',
                    'paid_at'          => $isCod ? now() : null,
                    'transaction_id'   => null,
                ]
            );

            $delivery->update(['payment_method' => $request->payment_method]);

            if ($payment->payment_method === 'card') {
                return redirect()->route('stripe.checkout', $payment->id);
            }

            if ($payment->payment_method === 'crypto') {
                return redirect()->route('nowpayments.checkout', $payment->id);
            }

            return redirect()->route('delivery.track', $delivery->id)
                ->with('success', 'Cash on delivery selected. Please pay the driver on arrival.');
        } catch (\Throwable $e) {
            Log::error('Payment creation failed', [
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Payment failed. Please try again.');
        }
    }

    /**
     * Landing page after an external (crypto) checkout. The payment itself is
     * confirmed by the provider's webhook, never by this redirect.
     */
    public function returned(Payment $payment)
    {
        $this->authorizeDelivery($payment->delivery);

        $message = $payment->payment_status === 'confirmed'
            ? 'Payment confirmed. Thank you!'
            : 'Thanks! Your payment will be marked as paid as soon as the provider confirms it.';

        return redirect()->route('delivery.track', $payment->delivery_id)->with('success', $message);
    }

    /**
     * Price split, all in USD.
     */
    private function breakdown(DeliveryRequest $delivery): array
    {
        $base = round((float) $delivery->price + (float) ($delivery->extra_charge ?? 0), 2);

        $platformPercentage = 0.10;
        $driverPercentage = 0.90;
        $centerPercentage = 0.0;

        if ($delivery->center_id ?? false) {
            $driverPercentage = 0.80;
            $centerPercentage = 0.10;
        }

        return [
            'base' => $base,
            'platform_fee' => round($base * $platformPercentage, 2),
            'driver_share' => round($base * $driverPercentage, 2),
            'center_share' => round($base * $centerPercentage, 2),
        ];
    }

    private function authorizeDelivery(?DeliveryRequest $delivery): void
    {
        abort_unless($delivery && $delivery->isOwnedByClient(auth()->user()), 403);
    }
}
