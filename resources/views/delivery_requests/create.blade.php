@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create a Delivery Request</h2>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Display validation errors in red -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    <!-- Form to create delivery request -->
    <form action="{{ route('delivery_requests.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Client Dropdown -->
        <div class="form-group">
            <label for="client_id">Client</label>
            <select name="client_id" id="client_id" class="form-control" required>
                <option value="">Select a Client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        Client ID: {{ $client->id }} - {{ $client->user_id ?? 'No Client Name' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Driver Dropdown -->
        <div class="form-group">
            <label for="driver_id">Driver (Optional)</label>
            <select name="driver_id" id="driver_id" class="form-control">
                <option value="">Select a Driver</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                        {{ $driver->vehicle_brand ?? 'No Vehicle Brand' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Package Dimensions -->
        <div class="form-group">
            <label for="length_cm">Length (cm)</label>
            <input type="number" class="form-control" id="length_cm" name="length_cm" value="{{ old('length_cm') }}" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="width_cm">Width (cm)</label>
            <input type="number" class="form-control" id="width_cm" name="width_cm" value="{{ old('width_cm') }}" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="height_cm">Height (cm)</label>
            <input type="number" class="form-control" id="height_cm" name="height_cm" value="{{ old('height_cm') }}" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="package_weight">Package Weight (kg)</label>
            <input type="number" class="form-control" id="package_weight" name="package_weight" value="{{ old('package_weight') }}" step="0.01" required>
        </div>

        <!-- Pricing Model Selector -->
        <div class="form-group">
            <label for="pricing_model">Pricing Model</label>
            <select name="pricing_model" id="pricing_model" class="form-control" required>
                <option value="fixed" {{ old('pricing_model') == 'fixed' ? 'selected' : '' }}>Fixed Price</option>
                <option value="per_km" {{ old('pricing_model') == 'per_km' ? 'selected' : '' }}>Per Kilometer</option>
            </select>
        </div>

        <!-- Distance (Optional for per_km) -->
        <div class="form-group">
            <label for="distance_km">Distance (km)</label>
            <input type="number" class="form-control" id="distance_km" name="distance_km" value="{{ old('distance_km') }}" step="0.01">
        </div>

        <!-- Calculate Price Button -->
        <div class="form-group">
            <button type="button" class="btn btn-info" id="calculate_price_button">Calculate Price</button>
        </div>

        <!-- Display Calculated Price -->
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" step="0.01" readonly required>
        </div>

        <!-- Urgency Level -->
        <div class="form-group">
            <label for="urgency_level">Urgency Level</label>
            <select name="urgency_level" id="urgency_level" class="form-control" required>
                <option value="normal" {{ old('urgency_level') == 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="urgent" {{ old('urgency_level') == 'urgent' ? 'selected' : '' }}>Urgent</option>
            </select>
        </div>

        <!-- Delivery Date -->
        <div class="form-group">
            <label for="delivery_date">Delivery Date</label>
            <input type="datetime-local" class="form-control" id="delivery_date" name="delivery_date" value="{{ old('delivery_date') }}" required>
        </div>

        <!-- Payment Method -->
        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-control" required>
                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                <option value="crypto" {{ old('payment_method') == 'crypto' ? 'selected' : '' }}>Crypto</option>
                <option value="cod" {{ old('payment_method') == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
            </select>
        </div>

        <!-- Payment Status -->
        <div class="form-group">
            <label for="is_paid">Is Paid</label>
            <input type="checkbox" id="is_paid" name="is_paid" value="1" {{ old('is_paid') ? 'checked' : '' }}>
        </div>

        <!-- Notes -->
        <div class="form-group">
            <label for="note">Note</label>
            <textarea class="form-control" id="note" name="note">{{ old('note') }}</textarea>
        </div>

        <!-- Contact Phone -->
        <div class="form-group">
            <label for="pickup_contact_name">Pickup Contact Name</label>
            <input type="text" class="form-control" id="pickup_contact_name" name="pickup_contact_name" value="{{ old('pickup_contact_name') }}">
        </div>

        <div class="form-group">
            <label for="pickup_contact_phone">Pickup Contact Phone</label>
            <input type="text" class="form-control" id="pickup_contact_phone" name="pickup_contact_phone" value="{{ old('pickup_contact_phone') }}">
        </div>

        <div class="form-group">
            <label for="dropoff_contact_name">Dropoff Contact Name</label>
            <input type="text" class="form-control" id="dropoff_contact_name" name="dropoff_contact_name" value="{{ old('dropoff_contact_name') }}">
        </div>

        <div class="form-group">
            <label for="dropoff_contact_phone">Dropoff Contact Phone</label>
            <input type="text" class="form-control" id="dropoff_contact_phone" name="dropoff_contact_phone" value="{{ old('dropoff_contact_phone') }}">
        </div>

        <!-- Scheduled Pickup At -->
        <div class="form-group">
            <label for="scheduled_pickup_at">Scheduled Pickup At</label>
            <input type="datetime-local" class="form-control" id="scheduled_pickup_at" name="scheduled_pickup_at" value="{{ old('scheduled_pickup_at') }}">
        </div>

        <!-- Cancellation Reason -->
        <div class="form-group">
            <label for="cancellation_reason">Cancellation Reason</label>
            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason">{{ old('cancellation_reason') }}</textarea>
        </div>

        <!-- Contact Phone -->
        <div class="form-group">
            <label for="contact_phone">Contact Phone</label>
            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}">
        </div>

        <!-- Assigned At -->
        <div class="form-group">
            <label for="assigned_at">Assigned At</label>
            <input type="datetime-local" class="form-control" id="assigned_at" name="assigned_at" value="{{ old('assigned_at') }}">
        </div>

        <!-- Completed At -->
        <div class="form-group">
            <label for="completed_at">Completed At</label>
            <input type="datetime-local" class="form-control" id="completed_at" name="completed_at" value="{{ old('completed_at') }}">
        </div>

        <!-- Package Size -->
        <div class="form-group">
            <label for="package_size">Package Size (cm³)</label>
            <input type="number" class="form-control" id="package_size" name="package_size" value="{{ old('package_size') }}" step="0.01" readonly>
        </div>

        <!-- Package Volume -->
        <div class="form-group">
            <label for="package_volume">Package Volume (m³)</label>
            <input type="number" class="form-control" id="package_volume" name="package_volume" value="{{ old('package_volume') }}" step="0.01" readonly>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<script>
    document.getElementById('calculate_price_button').addEventListener('click', function() {
        const pricingModel = document.getElementById('pricing_model').value;
        const distanceKm = parseFloat(document.getElementById('distance_km').value);
        const lengthCm = parseFloat(document.getElementById('length_cm').value);
        const widthCm = parseFloat(document.getElementById('width_cm').value);
        const heightCm = parseFloat(document.getElementById('height_cm').value);
        const weightKg = parseFloat(document.getElementById('package_weight').value);

        let price = 0;

        if (pricingModel === 'fixed') {
            price = 100; // Example fixed price
        } else if (pricingModel === 'per_km') {
            const sizeFactor = (lengthCm * widthCm * heightCm) / 1000000;
            const weightFactor = weightKg * 10;
            price = (distanceKm * 5) + sizeFactor + weightFactor;
        }

        document.getElementById('price').value = price;

        // Calculate and set package size and volume
        const packageSize = lengthCm * widthCm * heightCm;
        const packageVolume = packageSize / 1000000; // Convert cm³ to m³

        document.getElementById('package_size').value = packageSize;
        document.getElementById('package_volume').value = packageVolume;
    });
</script>
@endsection
