@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Driver Profile</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('drivers.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Vehicle Type -->
        <div class="form-group">
            <label for="vehicle_type">Vehicle Type</label>
            <input type="text" class="form-control" name="vehicle_type" value="{{ old('vehicle_type') }}" required>
        </div>

        <!-- Vehicle Brand -->
        <div class="form-group">
            <label for="vehicle_brand">Vehicle Brand</label>
            <input type="text" class="form-control" name="vehicle_brand" value="{{ old('vehicle_brand') }}">
        </div>

        <!-- Vehicle Model -->
        <div class="form-group">
            <label for="vehicle_model">Vehicle Model</label>
            <input type="text" class="form-control" name="vehicle_model" value="{{ old('vehicle_model') }}">
        </div>

        <!-- Vehicle Color -->
        <div class="form-group">
            <label for="vehicle_color">Vehicle Color</label>
            <input type="text" class="form-control" name="vehicle_color" value="{{ old('vehicle_color') }}">
        </div>

        <!-- Vehicle Year -->
        <div class="form-group">
            <label for="vehicle_year">Vehicle Year</label>
            <input type="text" class="form-control" name="vehicle_year" value="{{ old('vehicle_year') }}">
        </div>

        <!-- Plate Number -->
        <div class="form-group">
            <label for="plate_number">Plate Number</label>
            <input type="text" class="form-control" name="plate_number" value="{{ old('plate_number') }}" required>
        </div>

        <!-- License Number -->
        <div class="form-group">
            <label for="license_number">License Number</label>
            <input type="text" class="form-control" name="license_number" value="{{ old('license_number') }}" required>
        </div>

        <!-- License Expiry -->
        <div class="form-group">
            <label for="license_expiry">License Expiry Date</label>
            <input type="date" class="form-control" name="license_expiry" value="{{ old('license_expiry') }}">
        </div>

        <!-- Registration Document -->
        <div class="form-group">
            <label for="registration_document">Registration Document (PDF/JPG/PNG)</label>
            <input type="file" class="form-control" name="registration_document">
        </div>

        <!-- Pricing Type -->
        <div class="form-group">
            <label for="pricing_type">Pricing Type</label>
            <select class="form-control" name="pricing_type" required>
                <option value="fixed" {{ old('pricing_type') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                <option value="per_km" {{ old('pricing_type') == 'per_km' ? 'selected' : '' }}>Per KM</option>
            </select>
        </div>

        <!-- Price per KM -->
        <div class="form-group">
            <label for="price_per_km">Price per KM</label>
            <input type="number" class="form-control" name="price_per_km" value="{{ old('price_per_km') }}" step="0.01">
        </div>

        <!-- Fixed Price -->
        <div class="form-group">
            <label for="fixed_price">Fixed Price</label>
            <input type="number" class="form-control" name="fixed_price" value="{{ old('fixed_price') }}" step="0.01">
        </div>

        <!-- Availability -->
        <div class="form-group">
            <label for="availability">Availability</label>
            <input type="text" class="form-control" name="availability" value="{{ old('availability') }}" placeholder="e.g., Monday to Friday, 9 AM to 5 PM">
        </div>

        <!-- Earnings -->
        <div class="form-group">
            <label for="earnings">Earnings</label>
            <input type="number" class="form-control" name="earnings" value="{{ old('earnings') }}" step="0.01">
        </div>

        <!-- Delivery Count -->
        <div class="form-group">
            <label for="delivery_count">Delivery Count</label>
            <input type="number" class="form-control" name="delivery_count" value="{{ old('delivery_count') }}">
        </div>

        <!-- KM Completed -->
        <div class="form-group">
            <label for="km_completed">KM Completed</label>
            <input type="number" class="form-control" name="km_completed" value="{{ old('km_completed') }}" step="0.01">
        </div>

        <!-- Rating Average -->
        <div class="form-group">
            <label for="rating_average">Rating Average</label>
            <input type="number" class="form-control" name="rating_average" value="{{ old('rating_average') }}" step="0.1" min="0" max="5">
        </div>

        <!-- Warning Count -->
        <div class="form-group">
            <label for="warning_count">Warning Count</label>
            <input type="number" class="form-control" name="warning_count" value="{{ old('warning_count') }}">
        </div>

        <!-- Visibility Status -->
        <div class="form-group">
            <label for="visibility_status">Visibility Status</label>
            <select class="form-control" name="visibility_status" required>
                <option value="visible" {{ old('visibility_status') == 'visible' ? 'selected' : '' }}>Visible</option>
                <option value="hidden" {{ old('visibility_status') == 'hidden' ? 'selected' : '' }}>Hidden</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Submit Profile</button>
    </form>
</div>
@endsection
