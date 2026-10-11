@extends('admin.layouts.main')

@section('container')
@include('partials.navbar')

<div class="container" style="margin-top: 110px; margin-bottom: 60px;">
    <h2 class="mb-4">Admin dashboard</h2>

    <div class="row">
        @include('partials.stat-card', ['label' => 'Clients', 'value' => $stats['clients']])
        @include('partials.stat-card', ['label' => 'Drivers', 'value' => $stats['drivers']])
        @include('partials.stat-card', ['label' => 'Deliveries', 'value' => $stats['deliveries']])
        @include('partials.stat-card', ['label' => 'Platform fees (paid)', 'value' => '$' . number_format($stats['platform_fees'], 2)])
    </div>

    <div class="mb-4">
        @foreach (['pending', 'accepted', 'in_progress', 'delivered', 'canceled'] as $status)
            <span class="me-3">@include('partials.status-badge', ['status' => $status]) {{ $statusCounts[$status] ?? 0 }}</span>
        @endforeach
        <span class="text-muted">· {{ $stats['confirmed_payments'] }} confirmed payments</span>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Latest deliveries</strong></div>
        <div class="card-body p-0">
            @if ($recent->isEmpty())
                <p class="p-3 mb-0">No deliveries yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Tracking code</th>
                                <th>Client</th>
                                <th>Driver</th>
                                <th>From → To</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $delivery)
                                <tr>
                                    <td>{{ $delivery->tracking_code }}</td>
                                    <td>{{ $delivery->client->user->name ?? '—' }}</td>
                                    <td>{{ $delivery->driver->user->name ?? 'Not assigned' }}</td>
                                    <td class="small">{{ $delivery->pickupLocation->address ?? '—' }}<br>→ {{ $delivery->dropoffLocation->address ?? '—' }}</td>
                                    <td>@include('partials.status-badge', ['status' => $delivery->status])</td>
                                    <td>{{ $delivery->payment ? strtoupper($delivery->payment->payment_method) . ' · ' . $delivery->payment->payment_status : 'none' }}</td>
                                    <td class="text-end"><a href="{{ route('delivery.track', $delivery->id) }}" class="btn btn-sm btn-info">View</a></td>
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
