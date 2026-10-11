@extends('layouts.main')

@section('container')

@include('partials.navbar')
<section class="register-photo mt-5">
    <div class="form-container">
        <h2 class="text-center"><strong>My Deliveries</strong></h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($deliveries->isEmpty())
            <div class="alert alert-info">You have no deliveries yet.</div>
        @else
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pickup</th>
                        <th>Dropoff</th>
                        <th>Status</th>
                        <th>Delivery date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->id }}</td>
                            <td>{{ $delivery->pickupLocation->address ?? '—' }}</td>
                            <td>{{ $delivery->dropoffLocation->address ?? '—' }}</td>
                            <td>@include('partials.status-badge', ['status' => $delivery->status])</td>
                            <td>{{ $delivery->delivery_date->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('delivery.track', $delivery->id) }}" class="btn btn-sm btn-info">
                                    {{ in_array($delivery->status, ['pending', 'accepted', 'in_progress']) ? 'Track' : 'View' }}
                                </a>
                                @if($delivery->status !== 'canceled' && !($delivery->payment && $delivery->payment->payment_status === 'confirmed'))
                                    <a href="{{ route('payment.form', $delivery->id) }}" class="btn btn-sm btn-warning">Pay</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</section>
@endsection
