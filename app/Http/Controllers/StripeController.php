<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class StripeController extends Controller
{
    public function checkout(Payment $payment)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $payment->currency->code,
                    'unit_amount' => intval($payment->amount * 100), // Stripe uses cents
                    'product_data' => [
                        'name' => 'Delivery Payment #'.$payment->delivery_id,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success', $payment->id),
            'cancel_url' => route('payment.form', $payment->delivery_id),
        ]);

        return redirect($session->url);
    }

    public function success(Payment $payment)
    {
        $payment->update([
            'payment_status' => 'confirmed',
            'paid_at' => now(),
        ]);

        return redirect()->route('homepage')->with('success', 'Payment successful and confirmed.');
    }
}
