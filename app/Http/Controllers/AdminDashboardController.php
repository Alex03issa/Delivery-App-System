<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;
use App\Models\Driver;
use App\Models\Payment;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $statusCounts = DeliveryRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'clients' => User::where('role', 'client')->count(),
            'drivers' => Driver::count(),
            'deliveries' => DeliveryRequest::count(),
            'confirmed_payments' => Payment::where('payment_status', 'confirmed')->count(),
            'platform_fees' => (float) Payment::where('payment_status', 'confirmed')->sum('platform_fee'),
        ];

        $recent = DeliveryRequest::with(['client.user', 'driver.user', 'pickupLocation', 'dropoffLocation', 'payment'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.index', [
            'title' => 'Admin Dashboard | ' . config('app.name'),
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'recent' => $recent,
        ]);
    }
}
