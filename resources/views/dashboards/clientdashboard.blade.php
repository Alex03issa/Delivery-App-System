@extends('layouts.main')

@section('container')
@include('partials.navbar')

<div class="container" style="margin-top: 110px; margin-bottom: 60px;">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <h2 class="mb-0">Welcome, {{ Auth::user()->name }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('delivery.create') }}" class="btn btn-warning">Request a delivery</a>
            <a href="{{ route('client.deliveries') }}" class="btn btn-outline-dark">All my deliveries</a>
            <a href="{{ route('chat') }}" class="btn btn-outline-dark">Messages</a>
        </div>
    </div>

    <div class="row">
        @include('partials.stat-card', ['label' => 'Deliveries', 'value' => $stats['total']])
        @include('partials.stat-card', ['label' => 'Active', 'value' => $stats['active']])
        @include('partials.stat-card', ['label' => 'Delivered', 'value' => $stats['delivered']])
        @include('partials.stat-card', ['label' => 'Awaiting payment', 'value' => $stats['unpaid']])
    </div>

    <div class="card shadow-sm mt-2">
        <div class="card-header bg-white"><strong>Recent deliveries</strong></div>
        <div class="card-body p-0">
            @if ($recent->isEmpty())
                <p class="p-3 mb-0">You have no deliveries yet.
                    <a href="{{ route('delivery.create') }}">Request your first one</a>.</p>
            @else
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Tracking code</th>
                                <th>From → To</th>
                                <th>Driver</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $delivery)
                                @php $paid = $delivery->payment && $delivery->payment->payment_status === 'confirmed'; @endphp
                                <tr>
                                    <td>{{ $delivery->tracking_code }}</td>
                                    <td class="small">{{ $delivery->pickupLocation->address ?? '—' }}<br>→ {{ $delivery->dropoffLocation->address ?? '—' }}</td>
                                    <td>{{ $delivery->driver->user->name ?? 'Not assigned' }}</td>
                                    <td>@include('partials.status-badge', ['status' => $delivery->status])</td>
                                    <td>${{ number_format($delivery->price + $delivery->extra_charge, 2) }}
                                        @if ($paid)<span class="badge bg-success">Paid</span>@endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('delivery.track', $delivery->id) }}" class="btn btn-sm btn-info">Track</a>
                                        @if (!$paid && $delivery->status !== 'canceled')
                                            <a href="{{ route('payment.form', $delivery->id) }}" class="btn btn-sm btn-warning">Pay</a>
                                        @endif
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
