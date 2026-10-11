<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRequest;
use App\Models\Driver;
use App\Models\Location;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Called by the driver's browser while a delivery is in progress.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'delivery_id' => 'required|integer|exists:delivery_requests,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $delivery = DeliveryRequest::findOrFail($data['delivery_id']);
        $user = $request->user();

        if (!$delivery->isAssignedToDriver($user)) {
            return response()->json(['error' => 'This delivery is not assigned to you.'], 403);
        }

        if ($delivery->status !== 'in_progress') {
            return response()->json(['error' => 'Delivery is not in progress.'], 422);
        }

        // Keep one "live" row per driver instead of one row per GPS ping.
        Location::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'live'],
            [
                'delivery_request_id' => $delivery->id,
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'location_updated_at' => now(),
            ]
        );

        return response()->json(['status' => 'updated']);
    }

    /**
     * Latest position of a driver (by user id). Visible to the driver
     * themselves, admins, and clients with an active delivery assigned to
     * that driver.
     */
    public function getLatest(Request $request, $driverId)
    {
        $driver = Driver::where('user_id', $driverId)->first();
        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        $user = $request->user();
        $allowed = $user->role === 'admin'
            || (int) $user->id === (int) $driver->user_id
            || ($user->client && DeliveryRequest::where('client_id', $user->client->id)
                ->where('driver_id', $driver->id)
                ->whereIn('status', ['accepted', 'in_progress'])
                ->exists());

        if (!$allowed) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $location = Location::where('user_id', $driver->user_id)
            ->where('type', 'live')
            ->latest('updated_at')
            ->first();

        if (!$location) {
            return response()->json(['lat' => null, 'lng' => null]);
        }

        return response()->json([
            'lat' => $location->lat,
            'lng' => $location->lng,
            'updated_at' => optional($location->location_updated_at)->toIso8601String(),
        ]);
    }
}
