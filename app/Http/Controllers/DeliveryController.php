<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;
use App\Models\DeliveryLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    public function create()
    {
        Log::info('Client opened delivery create page.', ['user_id' => Auth::id()]);
        return view('delivery.create', [
            'title' => 'Delivery Create'
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Delivery form submitted.', ['user_id' => Auth::id(), 'input' => $request->all()]);

        $request->validate([
            'pickup_location' => 'required|string',
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'dropoff_location' => 'required|string',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',

            'length_cm' => 'required|numeric|min:0',
            'width_cm' => 'required|numeric|min:0',
            'height_cm' => 'required|numeric|min:0',
            'package_weight' => 'required|numeric|min:0',
            'package_size' => 'required|in:small,medium,large',

            'urgency_level' => 'required|in:normal,urgent',
            'delivery_date' => 'required|date',
            'payment_method' => 'required|in:card,crypto,cod',
            'price' => 'required|numeric|min:0',
            'extra_charge' => 'nullable|numeric|min:0',

            'contact_phone' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        if (!$user || !$user->client) {
            Log::warning('Delivery submission failed: user is not a client.', ['user_id' => Auth::id()]);
            return redirect()->back()->with('error', 'You must be logged in as a client to make a delivery request.');
        }

        $delivery = DeliveryRequest::create([
            'client_id' => $user->client->id,
            'pickup_location' => $request->pickup_location,
            'dropoff_location' => $request->dropoff_location,

            'length_cm' => $request->length_cm,
            'width_cm' => $request->width_cm,
            'height_cm' => $request->height_cm,
            'package_volume' => $request->length_cm * $request->width_cm * $request->height_cm,
            'package_weight' => $request->package_weight,
            'package_size' => $request->package_size,

            'urgency_level' => $request->urgency_level,
            'delivery_date' => $request->delivery_date,
            'payment_method' => $request->payment_method,
            'price' => $request->price,
            'extra_charge' => $request->extra_charge ?? 0,

            'contact_phone' => $request->contact_phone,
            'note' => $request->note,

            'status' => 'pending',
        ]);

        Log::info('Delivery request created.', ['delivery_id' => $delivery->id, 'client_id' => $user->client->id]);

        DeliveryLocation::create([
            'delivery_id' => $delivery->id,
            'type' => 'pickup',
            'address' => $request->pickup_location,
            'lat' => $request->pickup_lat,
            'lng' => $request->pickup_lng,
        ]);
        
        DeliveryLocation::create([
            'delivery_id' => $delivery->id,
            'type' => 'dropoff',
            'address' => $request->dropoff_location,
            'lat' => $request->dropoff_lat,
            'lng' => $request->dropoff_lng,
        ]);
        
        return redirect()->route('delivery.track', $delivery->id)
            ->with('success', 'Delivery request created successfully!');
    }

    public function track($id)
    {
        $delivery = DeliveryRequest::with(['pickupLocation', 'dropoffLocation', 'driver'])->findOrFail($id);
        Log::info('Client is tracking delivery.', ['delivery_id' => $delivery->id, 'user_id' => Auth::id()]);
        return view('delivery.track', [
            'delivery' => $delivery,
            'title' => 'Delivery Create'
        ]);
    }
}
