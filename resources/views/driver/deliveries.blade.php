@extends('layouts.main')

@section('container')
<section class="register-photo mt-5">
    <div class="form-container">
        <h2 class="text-center"><strong>Assigned Deliveries</strong></h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($deliveries->isEmpty())
            <div class="alert alert-info">You have no assigned deliveries.</div>
        @else
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pickup</th>
                        <th>Dropoff</th>
                        <th>Status</th>
                        <th>Delivery Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->id }}</td>
                            <td>{{ $delivery->pickupLocation->address ?? '—' }}</td>
                            <td>{{ $delivery->dropoffLocation->address ?? '—' }}</td>
                            <td><span class="badge bg-primary">{{ ucfirst($delivery->status) }}</span></td>
                            <td>{{ $delivery->delivery_date->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('delivery.track', $delivery->id) }}" class="btn btn-sm btn-info">Track</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</section>
@endsection
