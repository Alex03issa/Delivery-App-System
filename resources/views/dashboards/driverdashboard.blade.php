@extends('layouts.main')

@section('container')
@include('partials.navbar')

<div class="container" style="margin-top: 110px; margin-bottom: 60px;">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="mb-0">Welcome, {{ Auth::user()->name }}</h2>
            <div class="text-muted">{{ ucfirst($driver->vehicle_type) }} · {{ $driver->plate_number }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('driver.available') }}" class="btn btn-warning">Find deliveries</a>
            <a href="{{ route('driver.deliveries') }}" class="btn btn-outline-dark">My deliveries</a>
            <a href="{{ route('chat') }}" class="btn btn-outline-dark">Messages</a>
        </div>
    </div>

    <div class="row">
        @include('partials.stat-card', ['label' => 'Active jobs', 'value' => $stats['active']])
        @include('partials.stat-card', ['label' => 'Delivered', 'value' => $stats['delivered']])
        @include('partials.stat-card', ['label' => 'Open requests', 'value' => $stats['available']])
        @include('partials.stat-card', ['label' => 'Earnings (paid)', 'value' => '$' . number_format($stats['earnings'], 2)])
    </div>

    <div class="card shadow-sm mt-2">
        <div class="card-header bg-white"><strong>Current jobs</strong></div>
        <div class="card-body p-0">
            @if ($activeDeliveries->isEmpty())
                <p class="p-3 mb-0">No active jobs. <a href="{{ route('driver.available') }}">Browse available deliveries</a>.</p>
            @else
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Pickup</th>
                                <th>Drop-off</th>
                                <th>When</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeDeliveries as $delivery)
                                <tr>
                                    <td>{{ $delivery->id }}</td>
                                    <td class="small">{{ $delivery->pickupLocation->address ?? '—' }}</td>
                                    <td class="small">{{ $delivery->dropoffLocation->address ?? '—' }}</td>
                                    <td>{{ $delivery->delivery_date->format('Y-m-d H:i') }}</td>
                                    <td>@include('partials.status-badge', ['status' => $delivery->status])</td>
                                    <td class="text-end">
                                        <a href="{{ route('delivery.track', $delivery->id) }}" class="btn btn-sm btn-info">Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
