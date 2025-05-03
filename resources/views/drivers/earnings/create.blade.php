@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Earning</h1>
    <form method="POST" action="{{ route('drivers.earnings.store') }}">
        @csrf
        <div class="form-group">
            <label for="driver_id">Driver</label>
            <select name="driver_id" class="form-control" required>
                <option value="">Select Driver</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}"
                        {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
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
                    <option value="{{ $delivery->id }}">
                        {{ $delivery->package_details }} - {{ $delivery->delivery_status }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="total_revenue">Total Revenue</label>
            <input type="text" name="total_revenue" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="commission">Commission</label>
            <input type="text" name="commission" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="pending_payment">Pending Payment</label>
            <input type="text" name="pending_payment" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Earning</button>
    </form>
</div>
@endsection
