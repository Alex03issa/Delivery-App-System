<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;
use App\Models\Driver;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    /**
     * Driver profile form (shown after the driver verifies their email).
     */
    public function show()
    {
        $user = Auth::user();

        if ($user->driver) {
            return redirect()->route('driver.dashboard');
        }

        return view('driver.details', [
            'user' => $user,
            'title' => 'Driver Details',
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->driver) {
            return redirect()->route('driver.dashboard')->with('success', 'Your driver profile already exists.');
        }

        $request->validate([
            'vehicle_type' => 'required|string|max:255',
            'vehicle_brand' => 'nullable|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:255',
            'vehicle_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'plate_number' => 'required|string|max:50',
            'license_number' => 'required|string|max:100',
            'license_expiry' => 'nullable|date',
            'pricing_type' => 'required|in:fixed,per_km',
            'price_per_km' => 'nullable|numeric|min:0',
            'fixed_price' => 'nullable|numeric|min:0',
            'registration_document' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('registration_document')) {
            $path = $request->file('registration_document')->store('documents', 'public');
        }

        Driver::create([
            'user_id' => $user->id,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_brand' => $request->vehicle_brand,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_color' => $request->vehicle_color,
            'vehicle_year' => $request->vehicle_year,
            'plate_number' => $request->plate_number,
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'registration_document' => $path,
            'pricing_type' => $request->pricing_type,
            'price_per_km' => $request->price_per_km,
            'fixed_price' => $request->fixed_price,
        ]);

        return redirect()->route('driver.dashboard')->with('success', 'Driver profile saved. You can now accept deliveries.');
    }

    public function dashboard()
    {
        $driver = Auth::user()->driver;

        if (!$driver) {
            return redirect()->route('driver.register.details')
                ->with('success', 'Please complete your driver profile first.');
        }

        $mine = DeliveryRequest::where('driver_id', $driver->id);

        $stats = [
            'active' => (clone $mine)->whereIn('status', ['accepted', 'in_progress'])->count(),
            'delivered' => (clone $mine)->where('status', 'delivered')->count(),
            'available' => DeliveryRequest::whereNull('driver_id')->where('status', 'pending')->count(),
            'earnings' => (float) Payment::where('payment_status', 'confirmed')
                ->whereIn('delivery_id', (clone $mine)->where('status', 'delivered')->select('id'))
                ->sum('driver_share'),
        ];

        $activeDeliveries = (clone $mine)
            ->with(['pickupLocation', 'dropoffLocation'])
            ->whereIn('status', ['accepted', 'in_progress'])
            ->orderBy('delivery_date')
            ->take(5)
            ->get();

        return view('dashboards.driverdashboard', [
            'title' => 'Driver Dashboard',
            'driver' => $driver,
            'stats' => $stats,
            'activeDeliveries' => $activeDeliveries,
        ]);
    }

    /**
     * Driver marks an in-progress delivery as delivered.
     */
    public function completeDelivery(DeliveryRequest $delivery)
    {
        if (!$delivery->isAssignedToDriver(Auth::user())) {
            abort(403);
        }

        if ($delivery->status !== 'in_progress') {
            return back()->with('error', 'Only deliveries in progress can be completed.');
        }

        $delivery->update([
            'status' => 'delivered',
            'completed_at' => now(),
        ]);

        // Cash on delivery is collected by the driver at hand-over.
        if ($delivery->payment && $delivery->payment->payment_method === 'cod') {
            $delivery->update(['is_paid' => true]);
        }

        return redirect()->route('driver.deliveries')->with('success', 'Delivery marked as delivered.');
    }
}
