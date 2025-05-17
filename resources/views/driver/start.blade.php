@extends('layouts.main')

@section('content')
<div class="container">
    <h4>Live Delivery #{{ $delivery->id }}</h4>
    <div id="map" style="height: 500px;" class="mb-3"></div>

    <button class="btn btn-danger" onclick="stopDelivery()">Stop Delivery</button>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD900K9mRZHe8UMJdJL4u8CaBtJGonpvzo"></script>

<script>
let watchId = null;
let map, marker;

// Map Center: Pickup location
const center = {
    lat: {{ $delivery->pickupLocation->lat }},
    lng: {{ $delivery->pickupLocation->lng }}
};

// Setup map
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        center: center,
        zoom: 14,
    });

    marker = new google.maps.Marker({
        map: map,
        position: center,
        title: "You (Driver)",
        icon: "https://maps.google.com/mapfiles/kml/shapes/cabs.png"
    });
}

// Send location to backend
function sendLocation(lat, lng) {
    fetch('/api/driver/update-location', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer {{ auth()->user()->createToken("driver")->plainTextToken }}'
        },
        body: JSON.stringify({ lat, lng })
    });
}

// Start watching location
function startDelivery() {
    if (!navigator.geolocation) {
        alert('Geolocation not supported by your browser');
        return;
    }

    watchId = navigator.geolocation.watchPosition(position => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        // Move marker
        marker.setPosition({ lat, lng });
        map.panTo({ lat, lng });

        // Send to backend
        sendLocation(lat, lng);
    }, error => {
        alert('Unable to retrieve location');
        console.error(error);
    }, {
        enableHighAccuracy: true,
        maximumAge: 1000,
        timeout: 5000
    });
}

function stopDelivery() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        alert('Stopped tracking');
    }
}

initMap();
startDelivery();
</script>
@endsection
