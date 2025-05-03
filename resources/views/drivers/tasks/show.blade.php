@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Delivery Task #{{ $delivery->id }} Details</h2>

    <p><strong>Package Details:</strong> {{ $delivery->package_details }}</p>
    <p><strong>Delivery Date:</strong> {{ $delivery->delivery_date }}</p>
    <p><strong>Client ID:</strong> {{ $delivery->client->id ?? 'N/A' }}</p>
    <p><strong>Status:</strong> {{ $delivery->delivery_status }}</p>

    <form action="{{ route('driver.tasks.update', $delivery->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="delivery_status">Update Status:</label>
            <select name="delivery_status" class="form-control">
                <option value="Accepted" {{ $delivery->delivery_status == 'Accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="InProgress" {{ $delivery->delivery_status == 'InProgress' ? 'selected' : '' }}>InProgress</option>
                <option value="Delivered" {{ $delivery->delivery_status == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="Canceled" {{ $delivery->delivery_status == 'Canceled' ? 'selected' : '' }}>Canceled</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success mt-3">Update Status</button>
        <a href="{{ route('driver.tasks.index') }}" class="btn btn-secondary mt-3">Back</a>
    </form>
</div>
@endsection
