@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Earning</h1>
    <form method="POST" action="{{ route('drivers.earnings.update', $earning->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="driver_id">Driver</label>
            <select name="driver_id" class="form-control" required>
                <option value="">Select Driver</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" 
                        {{ $earning->driver_id == $driver->id ? 'selected' : '' }}>
                        {{ $driver->vehicle_type }} - {{ $driver->vehicle_brand }} ({{ $driver->plate_number }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="delivery_id">Delivery</label>
            <select name="delivery_id" class="form-control" required>
                <option value="">Select Delivery</option>
                @foreach($deliveries as $delivery)
                    <option value="{{ $delivery->id }}" 
                        {{ $earning->delivery_id == $delivery->id ? 'selected' : '' }}>
                        {{ $delivery->package_details }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="total_revenue">Total Revenue</label>
            <input type="text" name="total_revenue" class="form-control" value="{{ $earning->total_revenue }}" required>
        </div>
        <div class="form-group">
            <label for="commission">Commission</label>
            <input type="text" name="commission" class="form-control" value="{{ $earning->commission }}" required>
        </div>
        <div class="form-group">
            <label for="pending_payment">Pending Payment</label>
            <input type="text" name="pending_payment" class="form-control" value="{{ $earning->pending_payment }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Earning</button>
    </form>
</div>
@endsection
