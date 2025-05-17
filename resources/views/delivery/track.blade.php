@extends('layouts.main')

@section('container')

@include('partials.navbar')
<section class="register-photo" style="margin-top: 80px;">
    <div class="form-container">
        <h2 class="text-center"><strong>Live Delivery Tracking</strong></h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-3">
            <p><strong>Status:</strong> {{ ucfirst($delivery->status) }}</p>
            <p><strong>Estimated Time of Arrival:</strong> <span id="eta">Calculating...</span></p>
        </div>

        @if ($delivery->driver && Auth::user()->role === 'client')
            <a href="{{ route('chat') }}?with={{ $delivery->driver->user_id }}" class="btn btn-primary mt-3 w-100">
                Chat with Driver
            </a>
        @elseif ($delivery->client && Auth::user()->role === 'driver')
            <a href="{{ route('chat') }}?with={{ $delivery->client->user_id }}" class="btn btn-primary mt-3 w-100">
                Chat with Client
            </a>
        @endif


        <div id="map" style="height: 300px; border-radius: 8px; border: 1px solid #ccc;"></div>

        @if (!$delivery->driver)
            <div class="alert alert-warning mt-3">Driver is not yet assigned to this delivery.</div>
        @endif

        {{-- Show Start button if accepted --}}
        @if ($delivery->status === 'accepted' && Auth::user()->driver && Auth::user()->driver->id === $delivery->driver_id)
            <form method="POST" action="{{ route('driver.delivery.start', $delivery->id) }}" class="mt-3">
                @csrf
                <button class="btn btn-success w-100">Start Delivery</button>
            </form>
        @endif
    </div>
</section>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD900K9mRZHe8UMJdJL4u8CaBtJGonpvzo&libraries=places"></script>

<script>
const pickup = { lat: {{ $delivery->pickupLocation->lat }}, lng: {{ $delivery->pickupLocation->lng }} };
const dropoff = { lat: {{ $delivery->dropoffLocation->lat }}, lng: {{ $delivery->dropoffLocation->lng }} };
const driverId = {{ $delivery->driver->user_id ?? 'null' }};
const deliveryStatus = '{{ $delivery->status }}';

let map, driverMarker, routeRenderer;

function initMap(initialPos) {
    map = new google.maps.Map(document.getElementById("map"), {
        center: initialPos,
        zoom: 13,
    });

    // Markers
    new google.maps.Marker({
        position: pickup,
        map,
        title: "Pickup Location",
        icon: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
    });

    new google.maps.Marker({
        position: dropoff,
        map,
        title: "Dropoff Location",
        icon: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
    });

    driverMarker = new google.maps.Marker({
        position: initialPos,
        map,
        title: "Driver",
        icon: "https://maps.google.com/mapfiles/kml/shapes/cabs.png"
    });

    routeRenderer = new google.maps.DirectionsRenderer({ suppressMarkers: true });
    routeRenderer.setMap(map);
}

function updateRouteAndETA(origin, destination) {
    const directionsService = new google.maps.DirectionsService();

    directionsService.route({
        origin,
        destination,
        travelMode: google.maps.TravelMode.DRIVING
    }, (result, status) => {
        if (status === 'OK') {
            routeRenderer.setDirections(result);
            const etaSeconds = result.routes[0].legs[0].duration.value;
            const etaMinutes = Math.round(etaSeconds / 60);
            document.getElementById("eta").innerText = etaMinutes + " min";
        }
    });
}

function updateDriverPosition() {
    if (!driverId) return;

    fetch(`/api/tracking/driver-location/${driverId}`)
        .then(res => res.json())
        .then(data => {
            const driverPos = {
                lat: parseFloat(data.lat),
                lng: parseFloat(data.lng)
            };

            if (!driverPos.lat || !driverPos.lng || isNaN(driverPos.lat) || isNaN(driverPos.lng)) {
                console.warn("Invalid driver position; falling back to pickup.");
                return;
            }

            driverMarker.setPosition(driverPos);
            map.panTo(driverPos);
            updateRouteAndETA(driverPos, dropoff);
        }).catch(err => console.error("Location fetch error:", err));
}

// Initial load
if (driverId) {
    fetch(`/api/tracking/driver-location/${driverId}`)
        .then(res => res.json())
        .then(data => {
            const initialPos = {
                lat: parseFloat(data.lat),
                lng: parseFloat(data.lng)
            };

            if (!initialPos.lat || !initialPos.lng || isNaN(initialPos.lat) || isNaN(initialPos.lng)) {
                console.warn("Fallback to pickup location.");
                initMap(pickup);
                return;
            }

            initMap(initialPos);
            updateRouteAndETA(initialPos, dropoff);
        }).catch(() => {
            initMap(pickup);
        });

    if (deliveryStatus === 'in_progress') {
        setInterval(updateDriverPosition, 5000);
    }
} else {
    initMap(pickup);
}

@if (Auth::user()->driver && $delivery->status === 'in_progress')
navigator.geolocation.watchPosition(function(position) {
    fetch("/api/tracking/update-location", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            delivery_id: {{ $delivery->id }},
            lat: position.coords.latitude,
            lng: position.coords.longitude
        })
    });
}, err => console.warn("Geolocation error", err), {
    enableHighAccuracy: true,
    maximumAge: 0,
    timeout: 5000
});
@endif

</script>
@endsection
