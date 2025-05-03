@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Earning Details</h1>
    <p><strong>Driver:</strong> {{ $earning->driver->vehicle_type }} - {{ $earning->driver->vehicle_brand }} ({{ $earning->driver->plate_number }})</p>
    <p><strong>Delivery:</strong> {{ $earning->delivery->package_details }}</p>
    <p><strong>Total Revenue:</strong> {{ $earning->total_revenue }}</p>
    <p><strong>Commission:</strong> {{ $earning->commission }}</p>
    <p><strong>Pending Payment:</strong> {{ $earning->pending_payment }}</p>
    <a href="{{ route('drivers.earnings.index') }}" class="btn btn-secondary">Back to Earnings</a>
</div>
@endsection
