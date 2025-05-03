@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add New Delivery</h2>

    <form action="{{ route('driver.tasks.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="driver_id">Driver</label>
            <select name="driver_id" id="driver_id" class="form-control" required>
                <option value="">Select Driver</option>
                <option value="2">Driver ID: 2</option> {{-- You can extend this with dynamic list if needed --}}
            </select>
        </div>

        <div class="form-group">
            <label for="client_id">Client</label>
            <select name="client_id" id="client_id" class="form-control" required>
                <option value="">Select Client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">Client ID: {{ $client->id }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="package_details">Package Details</label>
            <input type="text" class="form-control" id="package_details" name="package_details" required>
        </div>

        <div class="form-group">
            <label for="delivery_date">Delivery Date</label>
            <input type="date" class="form-control" id="delivery_date" name="delivery_date" required>
        </div>

        <div class="form-group">
            <label for="total_price">Total Price</label>
            <input type="number" step="0.01" class="form-control" id="total_price" name="total_price" required>
        </div>

        <button type="submit" class="btn btn-success">Add Delivery</button>
    </form>

    <a href="{{ route('driver.tasks.index') }}" class="btn btn-secondary mt-3">Back</a>
</div>
@endsection
