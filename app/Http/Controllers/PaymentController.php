<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Currency;
use App\Models\DeliveryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PaymentController extends Controller
{
    public function showPaymentForm(DeliveryRequest $delivery)
    {
        $currencies = Currency::all();
        
        $defaultCurrency = Currency::where('code', 'USD')->first();

        $baseAmount = $delivery->price + ($delivery->extra_charge ?? 0);

        $platformPercentage = 0.10;
        $driverPercentage = 0.90;
        $centerShare = 0;
        $hasCenterShare = false;

        if ($delivery->center_id ?? false) {
            $platformPercentage = 0.10;
            $driverPercentage = 0.80;
            $centerPercentage = 0.10;
            $centerShare = round($baseAmount * $centerPercentage, 2);
            $hasCenterShare = true;
        }

        $platformFee = round($baseAmount * $platformPercentage, 2);
        $driverShare = round($baseAmount * $driverPercentage, 2);

        return view('payments.form', [
            'delivery' => $delivery,
            'currencies' => $currencies,
            'baseAmount' => $baseAmount,
            'convertedAmount' => null,
            'platformFee' => $platformFee,
            'driverShare' => $driverShare,
            'centerShare' => $centerShare,
            'hasCenterShare' => $hasCenterShare,
            'defaultCurrency' => $defaultCurrency,
            'title' => 'Payment Page',
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Payment Store Request:', $request->all());
       
        $cleanAmount = floatval(str_replace(' ', '', $request->amount));
        $request->merge(['amount' => $cleanAmount]);
        
        $request->validate([
            'delivery_id' => 'required|exists:delivery_requests,id',
            'payment_method' => 'required|in:card,crypto,cod',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.0000000001',
            'conversion_rate' => 'required|numeric|min:0.0000000001',
        ]);

        $delivery = DeliveryRequest::findOrFail($request->delivery_id);
        \Log::info('Delivery Found:', ['id' => $delivery->id]);

        try {
            $payment = Payment::create([
                'delivery_id'      => $delivery->id,
                'payment_method'   => $request->payment_method,
                'currency_id'      => $request->currency_id,
                'amount'           => $cleanAmount,
                'converted_amount' => $cleanAmount,
                'conversion_rate'  => $request->conversion_rate,
                'platform_fee'     => $request->platform_fee ?? null,
                'driver_share'     => $request->driver_share ?? null,
                'center_share'     => $request->center_share ?? 0,
                'payment_status'   => $request->payment_method === 'cod' ? 'confirmed' : 'pending',
                'paid_at'          => $request->payment_method === 'cod' ? now() : null,
            ]);

            \Log::info('Payment Created:', ['id' => $payment->id]);

            if ($payment->payment_method === 'card') {
                return redirect()->route('stripe.checkout', $payment->id);
            }

            if ($payment->payment_method === 'crypto') {
                return redirect()->route('payment.crypto', $payment->id);
            }

            return redirect()->route('homepage')->with('success', 'Payment confirmed. Thank you!');

        } catch (\Throwable $e) {
            \Log::error('Payment creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Payment failed. Please try again.');
        }
    }


    public function card(Payment $payment)
    {
        return view('payments.card-checkout', compact('payment'));
    }

    public function crypto(Payment $payment)
    {
        return view('payments.crypto-checkout', compact('payment'));
    }

    public function confirm(Payment $payment)
    {
        $payment->update([
            'payment_status' => 'confirmed',
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Payment confirmed.');
    }
}
