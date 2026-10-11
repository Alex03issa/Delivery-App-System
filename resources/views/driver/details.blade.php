@extends('layouts.main')

@section('container')
    <section class="register-photo" style="padding-top: 80px;">
        <div class="form-container">
            <div class="image-holder"></div>

            <form method="POST" action="{{ route('driver.register.save', $user->id) }}" enctype="multipart/form-data">
                @csrf

                <h2 class="text-center mb-3"><strong>Welcome, {{ $user->name }}</strong></h2>
                <p class="text-center">Complete your driver profile to get started.</p>

                <div class="mb-3">
                    <input class="form-control" name="vehicle_type" placeholder="Vehicle Type" required>
                </div>

                <div class="mb-3">
                    <input class="form-control" name="vehicle_brand" placeholder="Vehicle Brand">
                </div>

                <div class="mb-3">
                    <input class="form-control" name="vehicle_model" placeholder="Vehicle Model">
                </div>

                <div class="mb-3">
                    <input class="form-control" name="vehicle_color" placeholder="Vehicle Color">
                </div>

                <div class="mb-3">
                    <input class="form-control" name="vehicle_year" placeholder="Vehicle Year">
                </div>

                <div class="mb-3">
                    <input class="form-control" name="plate_number" placeholder="Plate Number" required>
                </div>

                <div class="mb-3">
                    <input class="form-control" name="license_number" placeholder="License Number" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">License Expiry Date</label>
                    <input type="date" class="form-control" name="license_expiry">
                </div>

                <div class="mb-3">
                    <label class="form-label">Registration Document (Optional)</label>
                    <input type="file" name="registration_document" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Pricing Type</label>
                    <select class="form-control" name="pricing_type" required>
                        <option value="">-- Select Pricing Type --</option>
                        <option value="fixed">Fixed</option>
                        <option value="per_km">Per Km</option>
                    </select>
                </div>

                <div class="mb-3">
                    <input class="form-control" name="price_per_km" placeholder="Price per Km (if per_km)">
                </div>

                <div class="mb-3">
                    <input class="form-control" name="fixed_price" placeholder="Fixed Price (if fixed)">
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary d-block w-100" style="background: rgb(254,209,54);">
                        Submit Profile
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
