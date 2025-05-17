@extends('layouts.main')

@section('container')

@include('partials.navbar')
<section class="register-photo"  style="margin-top: 80px;">
    <div class="form-container">
        <form method="POST" action="{{ route('delivery.store') }}">
            @csrf

            <h2 class="text-center"><strong>Create Delivery Request</strong></h2>

            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Pickup --}}
            <div class="mb-3">
                <label>Pickup Address</label>
                <input type="text" name="pickup_location" id="pickup_address" class="form-control"
                       value="{{ old('pickup_location') }}" required>
                <div id="pickup_map" style="height: 180px; border: 1px solid #ccc; margin-top: 10px; margin-bottom: 10px;"></div>
                <input type="hidden" name="pickup_lat" id="pickup_lat" value="{{ old('pickup_lat') }}">
                <input type="hidden" name="pickup_lng" id="pickup_lng" value="{{ old('pickup_lng') }}">
            </div>

            {{-- Dropoff --}}
            <div class="mb-3">
                <label>Dropoff Address</label>
                <input type="text" name="dropoff_location" id="dropoff_address" class="form-control"
                       value="{{ old('dropoff_location') }}" required>
                <div id="dropoff_map" style="height: 180px; border: 1px solid #ccc; margin-top: 10px; margin-bottom: 10px;"></div>
                <input type="hidden" name="dropoff_lat" id="dropoff_lat" value="{{ old('dropoff_lat') }}">
                <input type="hidden" name="dropoff_lng" id="dropoff_lng" value="{{ old('dropoff_lng') }}">
            </div>

            {{-- Package Info --}}
            <div class="mb-3">
                <input type="number" class="form-control" name="length_cm" placeholder="Length (cm)"
                       value="{{ old('length_cm') }}" step="0.01" required>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" name="width_cm" placeholder="Width (cm)"
                       value="{{ old('width_cm') }}" step="0.01" required>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" name="height_cm" placeholder="Height (cm)"
                       value="{{ old('height_cm') }}" step="0.01" required>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" name="package_weight" placeholder="Weight (kg)"
                       value="{{ old('package_weight') }}" step="0.01" required>
            </div>
            <div class="mb-3">
                <select name="package_size" class="form-control" required>
                    <option value="">Select Package Size</option>
                    <option value="small" {{ old('package_size') == 'small' ? 'selected' : '' }}>Small</option>
                    <option value="medium" {{ old('package_size') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="large" {{ old('package_size') == 'large' ? 'selected' : '' }}>Large</option>
                </select>
            </div>

            {{-- Delivery Info --}}
            <div class="mb-3">
                <select name="urgency_level" class="form-control" required>
                    <option value="">Urgency Level</option>
                    <option value="normal" {{ old('urgency_level') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="urgent" {{ old('urgency_level') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="mb-3">
                <input type="datetime-local" class="form-control" name="delivery_date"
                       value="{{ old('delivery_date') }}" required>
            </div>
            <div class="mb-3">
                <select name="payment_method" class="form-control" required>
                    <option value="">Payment Method</option>
                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                    <option value="crypto" {{ old('payment_method') == 'crypto' ? 'selected' : '' }}>Crypto</option>
                    <option value="cod" {{ old('payment_method') == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                </select>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" name="price" placeholder="Price ($)"
                       value="{{ old('price') }}" step="0.01" required>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" name="extra_charge" placeholder="Extra Charge ($)"
                       value="{{ old('extra_charge', 0) }}" step="0.01">
            </div>

            {{-- Contact --}}
            <div class="mb-3">
                <input type="text" class="form-control" name="contact_phone" placeholder="Contact Phone"
                       value="{{ old('contact_phone') }}">
            </div>
            <div class="mb-3">
                <textarea class="form-control" name="note" placeholder="Optional Notes" rows="3">{{ old('note') }}</textarea>
            </div>

            <div class="mb-3">
                <button class="btn btn-primary d-block w-100" type="submit">Submit Delivery</button>
            </div>
        </form>
    </div>
</section>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD900K9mRZHe8UMJdJL4u8CaBtJGonpvzo&libraries=places"></script>

<script>
function setupMap(mapId, latId, lngId, addressId) {
    const center = { lat: 33.8938, lng: 35.5018 };
    const map = new google.maps.Map(document.getElementById(mapId), { center, zoom: 13 });
    let marker = null;
    const geocoder = new google.maps.Geocoder();

    map.addListener("click", (e) => {
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();

        if (!marker) {
            marker = new google.maps.Marker({ map });
        }
        marker.setPosition(e.latLng);

        document.getElementById(latId).value = lat;
        document.getElementById(lngId).value = lng;

        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
            if (status === "OK" && results[0]) {
                document.getElementById(addressId).value = results[0].formatted_address;
            }
        });
    });
}

window.onload = () => {
    setupMap("pickup_map", "pickup_lat", "pickup_lng", "pickup_address");
    setupMap("dropoff_map", "dropoff_lat", "dropoff_lng", "dropoff_address");
};
</script>
@endsection
