<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class DriverTrackingController extends Controller
{
    /**
     * Store the driver's current position (one "live" row per driver).
     */
    public function updateLocation(Request $request)
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        Location::updateOrCreate(
            ['user_id' => $request->user()->id, 'type' => 'live'],
            [
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'location_updated_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }
}
