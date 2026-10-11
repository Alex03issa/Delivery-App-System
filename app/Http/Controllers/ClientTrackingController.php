<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;

class ClientTrackingController extends Controller
{
    public function track($id)
    {
        $delivery = DeliveryRequest::with(['pickupLocation', 'dropoffLocation', 'driver'])->findOrFail($id);
        abort_unless($delivery->isOwnedByClient(auth()->user()), 403);

        return view('client.track', [
            'delivery' => $delivery,
            'title' => 'Track Delivery'
        ]);
    }
}
