<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    public function show($user_id)
    {
        $user = User::findOrFail($user_id);
        return view('driver.details', [
            'user' => $user,
            'title' => 'Home'
        ]);
    
    }

    public function store(Request $request, $user_id)
    {
        $request->validate([
            'vehicle_type' => 'required|string',
            'plate_number' => 'required|string',
            'license_number' => 'required|string',
            'pricing_type' => 'required|in:fixed,per_km',
            'price_per_km' => 'nullable|numeric',
            'fixed_price' => 'nullable|numeric',
            'registration_document' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('registration_document')) {
            $path = $request->file('registration_document')->store('documents', 'public');
        }

        Driver::create([
            'user_id' => $user_id,
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

        return redirect()->route('driver.dashboard')->with('success', 'Driver profile submitted. Awaiting admin approval.');
    }
}
