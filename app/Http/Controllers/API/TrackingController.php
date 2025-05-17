<?php

namespace App\Http\Controllers\API;

use App\Models\Location;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrackingController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'delivery_id' => 'required|exists:delivery_requests,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        Location::create([
            'user_id' => auth()->id(),
            'delivery_request_id' => $request->delivery_id,
            'type' => 'live',
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

        return response()->json(['status' => 'updated']);
    }

    public function getLatest($driverId)
    {
        $driver = Driver::where('user_id', $driverId)->first();
        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        $location = Location::where('user_id', $driverId)->latest()->first();

        if (!$location) {
            return response()->json(['lat' => null, 'lng' => null]);
        }

        return response()->json([
            'lat' => $location->lat,
            'lng' => $location->lng,
        ]);
    }
}
