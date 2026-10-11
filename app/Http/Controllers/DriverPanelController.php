<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;
use Illuminate\Support\Facades\Auth;

class DriverPanelController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $driver = $user->driver;
    
        if (!$driver) {
            return redirect()->route('driver.register.details')
                ->with('success', 'Please complete your driver profile first.');
        }
    
        $deliveries = DeliveryRequest::with(['pickupLocation', 'dropoffLocation'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['accepted', 'in_progress'])
            ->orderBy('delivery_date')
            ->get();
    
        return view('driver.deliveries', [
            'deliveries' => $deliveries,
            'title' => 'Driver Deliveries'
        ]);
    }
    
    public function start($id)
    {
        $delivery = DeliveryRequest::with(['pickupLocation', 'dropoffLocation'])->findOrFail($id);
        abort_unless($delivery->isAssignedToDriver(Auth::user()), 403);

        if ($delivery->status !== 'in_progress') {
            return redirect()->route('delivery.track', $delivery->id);
        }

        return view('driver.start', [
            'delivery' => $delivery,
            'title' => 'Delivery Start'
        ]);
    }


    public function startDelivery($id)
    {
        $user = Auth::user();
        $driver = $user->driver;

        if (!$driver) {
            return redirect()->back()->with('error', 'You must be a driver.');
        }

        $delivery = DeliveryRequest::where('id', $id)
            ->where('driver_id', $driver->id)
            ->where('status', 'accepted')
            ->firstOrFail();

        $delivery->update([
            'status' => 'in_progress',
        ]);

        return redirect()->route('delivery.track', $delivery->id)->with('success', 'Delivery started!');
    }


    public function available()
    {
        $deliveries = DeliveryRequest::with(['pickupLocation', 'dropoffLocation'])
            ->whereNull('driver_id')
            ->where('status', 'pending')
            ->get();

        return view('driver.available', [
            'deliveries' => $deliveries,
            'title' => 'Available Deliveries',
        ]);
    }

    public function accept($id)
    {
        $user = Auth::user();
        $driver = $user->driver;

        if (!$driver) {
            return redirect()->route('driver.register.details')
                ->with('success', 'Please complete your driver profile first.');
        }

        $delivery = DeliveryRequest::where('id', $id)
            ->whereNull('driver_id')
            ->where('status', 'pending')
            ->firstOrFail();

        $delivery->update([
            'driver_id' => $driver->id,
            'status' => 'accepted',
            'assigned_at' => now(),
        ]);

        return redirect()->route('driver.deliveries')->with('success', 'Delivery accepted!');
    }

}
