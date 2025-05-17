<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\DeliveryRequest;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    public function show()
    {
        return view('dashboards.clientdashboard', ['title' => 'Client Dashboard']);
    }

    public function index()
    {
        $client = Auth::user()->client;

        if (!$client) {
            return redirect()->back()->with('error', 'You must be a client to access this page.');
        }

        $deliveries = DeliveryRequest::where('client_id', $client->id)
                        ->latest()
                        ->with(['pickupLocation', 'dropoffLocation', 'driver'])
                        ->get();

        return view('client.deliveries.index', [
            'deliveries' => $deliveries,
            'title' => 'Driver Deliveries'
        ]);
    }
}
