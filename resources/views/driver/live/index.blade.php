@extends('layouts.main')

@section('container')
<section class="register-photo mt-5">
    <div class="form-container">
        <h2 class="text-center"><strong>Driver Live Tracking</strong></h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(!$delivery)
            <div class="alert alert-info">You have no assigned delivery at the moment.</div>
        @else
            <div class="mb-3">
                <p><strong>Delivery ID:</strong> {{ $delivery->id }}</p>
                <p><strong>Status:</strong> <span class="badge bg-primary">{{ ucfirst($delivery->status) }}</span></p>
                <p><strong>Pickup:</strong> {{ $delivery->pickup_location }}</p>
                <p><strong>Dropoff:</strong> {{ $delivery->dropoff_location }}</p>
            </div>

            {{-- Start Button --}}
            @if($delivery->status === 'accepted')
                <form method="POST" action="{{ route('driver.live.start', $delivery->id) }}">
                    @csrf
                    <button class="btn btn-success mb-4 w-100" type="submit">Start Delivery</button>
                </form>
            @endif

            {{-- Live Map --}}
            @if($delivery->status === 'in_progress')
                <div id="map" style="height: 400px; border: 1px solid #ccc; border-radius: 8px;"></div>
            @endif
        @endif
    </div>
</section>

@if($delivery && $delivery->status === 'in_progress')
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD900K9mRZHe8UMJdJL4u8CaBtJGonpvzo"></script>

    <script>
        const deliveryId = {{ $delivery->id }};
        const driverId = {{ auth()->user()->id }};
        let map, marker;

        function initMap(position) {
            const pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            map = new google.maps.Map(document.getElementById("map"), {
                center: pos,
                zoom: 14
            });

            marker = new google.maps.Marker({
                position: pos,
                map: map,
                title: "Your Location",
                icon: "https://maps.google.com/mapfiles/kml/shapes/cabs.png"
            });

            sendLocation(pos);
        }

        function sendLocation(pos) {
            fetch('/api/tracking/update-location', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    user_id: driverId,
                    delivery_request_id: deliveryId,
                    lat: pos.lat,
                    lng: pos.lng
                })
            });
        }

        function updatePosition() {
            navigator.geolocation.getCurrentPosition(position => {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                marker.setPosition(pos);
                map.panTo(pos);
                sendLocation(pos);
            });
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(initMap);
            setInterval(updatePosition, 5000); // update every 5 seconds
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    </script>
@endif
@endsection
