<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;

class ClientTrackingController extends Controller
{
    public function track($id)
    {
        $delivery = DeliveryRequest::with(['pickupLocation', 'dropoffLocation', 'driver'])->findOrFail($id);
        return view('client.track', [
            'delivery' => $delivery,
            'title' => 'Driver Delivery'
        ]);
    }
}
