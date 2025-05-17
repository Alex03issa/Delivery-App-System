<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;

class DriverTrackingController extends Controller
{
    public function updateLocation(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        Location::updateOrCreate(
            ['user_id' => auth()->id(), 'type' => 'live'],
            [
                'lat' => $request->lat,
                'lng' => $request->lng,
                'location_updated_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }
}
