<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;
use App\Models\Client;
use App\Models\Driver;
use Illuminate\Http\Request;

class DeliveryRequestController extends Controller
{
    public function create()
    {
        // Fetch all clients and drivers
        $clients = Client::all();
        $drivers = Driver::all();

        return view('delivery_requests.create', compact('clients', 'drivers'));
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'length_cm' => 'required|numeric',
            'width_cm' => 'required|numeric',
            'height_cm' => 'required|numeric',
            'package_weight' => 'required|numeric',
            'pricing_model' => 'required|in:fixed,per_km',
            'distance_km' => 'nullable|numeric',
            'urgency_level' => 'required|in:normal,urgent',
            'delivery_date' => 'required|date',
            'payment_method' => 'required|in:card,crypto,cod',
            'is_paid' => 'nullable|boolean',
            'note' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'pickup_contact_name' => 'nullable|string',
            'pickup_contact_phone' => 'nullable|string',
            'dropoff_contact_name' => 'nullable|string',
            'dropoff_contact_phone' => 'nullable|string',
        ]);
    
        // Calculate the price based on the input data
        $pricingModel = $request->input('pricing_model');
        $distanceKm = $request->input('distance_km');
        $lengthCm = $request->input('length_cm');
        $widthCm = $request->input('width_cm');
        $heightCm = $request->input('height_cm');
        $weightKg = $request->input('package_weight');
    
        $price = 0;
    
        if ($pricingModel === 'fixed') {
            $price = 100; // Example fixed price
        } else if ($pricingModel === 'per_km') {
            $sizeFactor = ($lengthCm * $widthCm * $heightCm) / 1000000; // Volume factor (m^3)
            $weightFactor = $weightKg * 10; // Weight factor (example)
            $price = ($distanceKm * 5) + $sizeFactor + $weightFactor;
        }
    
        // Create a new delivery request
        $deliveryRequest = new DeliveryRequest($validated);
        $deliveryRequest->price = $price; 
        $deliveryRequest->tracking_code = 'DLV-' . strtoupper(uniqid('OKBCEX'));
        $deliveryRequest->save();
    
        // Redirect back with success message
        return redirect()->route('delivery_requests.create')->with('success', 'Delivery request created successfully!');
    }
    
}
