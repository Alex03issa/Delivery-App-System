@extends('layouts.main')

@section('container')
@include('partials.navbar')

<div class="container mt-5">
    <h3>Tracking Delivery #{{ $delivery->id }}</h3>

    <p>Status: <strong id="status-text">{{ ucfirst($delivery->status) }}</strong></p>
    <p>Estimated Time of Arrival (ETA): <span id="eta">Calculating...</span></p>
    <div id="offline-alert" class="alert alert-warning d-none">Driver might be offline or lost connection.</div>
    @if ($delivery->driver && Auth::user()->role === 'client')
        <a href="{{ route('chat') }}?with={{ $delivery->driver->user_id }}" class="btn btn-primary mt-3 w-100">
            Chat with Driver
        </a>
    @elseif ($delivery->client && Auth::user()->role === 'driver')
        <a href="{{ route('chat') }}?with={{ $delivery->client->user_id }}" class="btn btn-primary mt-3 w-100">
            Chat with Client
        </a>
    @endif

    @if ($delivery->status === 'delivered')
        <div class="alert alert-success mt-3">This delivery has been marked as <strong>Delivered</strong>.</div>
    @endif

    <div id="map" style="height: 500px; border: 1px solid #ccc; border-radius: 8px;" class="mt-4"></div>
</div>

{{-- Google Maps --}}
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD900K9mRZHe8UMJdJL4u8CaBtJGonpvzo"></script>

<script>
const driverId = {{ $delivery->driver->user_id ?? 'null' }};
const pickup = { lat: {{ $delivery->pickupLocation->lat }}, lng: {{ $delivery->pickupLocation->lng }} };
const dropoff = { lat: {{ $delivery->dropoffLocation->lat }}, lng: {{ $delivery->dropoffLocation->lng }} };

let map, driverMarker, routeRenderer;
let lastUpdate = Date.now();

function initMap(initialPos) {
    map = new google.maps.Map(document.getElementById("map"), {
        center: initialPos,
        zoom: 14,
    });

    // Mark Pickup
    new google.maps.Marker({
        position: pickup,
        map,
        title: "Pickup",
        icon: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
    });

    // Mark Dropoff
    new google.maps.Marker({
        position: dropoff,
        map,
        title: "Dropoff",
        icon: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
    });

    // Mark Driver
    driverMarker = new google.maps.Marker({
        position: initialPos,
        map,
        title: "Driver",
        icon: "https://maps.google.com/mapfiles/kml/shapes/cabs.png"
    });

    // Setup route renderer
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

function updateMapAndETA() {
    fetch(`/api/tracking/driver-location/${driverId}`)
        .then(res => res.json())
        .then(data => {
            const driverPos = {
                lat: parseFloat(data.lat),
                lng: parseFloat(data.lng)
            };

            if (!driverPos.lat || !driverPos.lng || isNaN(driverPos.lat) || isNaN(driverPos.lng)) return;

            driverMarker.setPosition(driverPos);
            map.panTo(driverPos);
            updateRouteAndETA(driverPos, dropoff);

            lastUpdate = Date.now();
        })
        .catch(console.error);
}

function checkDriverOnlineStatus() {
    const secondsSinceUpdate = (Date.now() - lastUpdate) / 1000;
    const alertBox = document.getElementById("offline-alert");
    alertBox.classList.toggle("d-none", secondsSinceUpdate <= 30);
}

// Initialize map with current driver location
fetch(`/api/tracking/driver-location/${driverId}`)
    .then(res => res.json())
    .then(data => {
        const initialPos = {
            lat: parseFloat(data.lat),
            lng: parseFloat(data.lng)
        };

        if (!initialPos.lat || !initialPos.lng || isNaN(initialPos.lat) || isNaN(initialPos.lng)) {
            initMap(pickup);
        } else {
            initMap(initialPos);
            updateRouteAndETA(initialPos, dropoff);
        }
    })
    .catch(() => initMap(pickup));


function updateDriverPosition() {
    fetch(`/api/tracking/driver-location/${driverId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.lat || !data.lng) return;

            const driverPos = {
                lat: parseFloat(data.lat),
                lng: parseFloat(data.lng)
            };

            driverMarker.setPosition(driverPos);
            updateRouteAndETA(driverPos, dropoff);
        });
}

setInterval(updateMapAndETA, 5000);
setInterval(checkDriverOnlineStatus, 5000);
</script>
@endsection
