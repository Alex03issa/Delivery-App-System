<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\DeliveryRequest;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    public function show()
    {
        $client = Auth::user()->client;
        $deliveries = $client
            ? DeliveryRequest::where('client_id', $client->id)
            : DeliveryRequest::whereRaw('1 = 0');

        $stats = [
            'total' => (clone $deliveries)->count(),
            'active' => (clone $deliveries)->whereIn('status', ['pending', 'accepted', 'in_progress'])->count(),
            'delivered' => (clone $deliveries)->where('status', 'delivered')->count(),
            'unpaid' => (clone $deliveries)->where('status', '!=', 'canceled')
                ->whereDoesntHave('payment', fn ($q) => $q->where('payment_status', 'confirmed'))
                ->count(),
        ];

        $recent = (clone $deliveries)
            ->with(['pickupLocation', 'dropoffLocation', 'driver.user', 'payment'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboards.clientdashboard', [
            'title' => 'Client Dashboard',
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }

    public function index()
    {
        $client = Auth::user()->client;

        if (!$client) {
            abort(403, 'You must be a client to access this page.');
        }

        $deliveries = DeliveryRequest::where('client_id', $client->id)
                        ->latest()
                        ->with(['pickupLocation', 'dropoffLocation', 'driver', 'payment'])
                        ->get();

        return view('client.deliveries.index', [
            'deliveries' => $deliveries,
            'title' => 'My Deliveries'
        ]);
    }
}
