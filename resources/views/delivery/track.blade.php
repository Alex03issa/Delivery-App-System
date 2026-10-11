@extends('layouts.main')

@section('container')

@include('partials.navbar')
<section class="register-photo" style="margin-top: 80px;">
    <div class="form-container">
        <h2 class="text-center"><strong>Live Delivery Tracking</strong></h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="mb-3">
            <p><strong>Tracking code:</strong> {{ $delivery->tracking_code }}</p>
            <p><strong>From:</strong> {{ $delivery->pickupLocation->address ?? '—' }}<br>
               <strong>To:</strong> {{ $delivery->dropoffLocation->address ?? '—' }}</p>
            @if ($delivery->dropoff_contact_phone)
                <p><strong>Contact phone:</strong> {{ $delivery->dropoff_contact_phone }}</p>
            @endif
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}</p>
            <p><strong>Price:</strong> ${{ number_format($delivery->price + ($delivery->extra_charge ?? 0), 2) }}
                @php $payment = $delivery->payment; @endphp
                @if ($payment && $payment->payment_status === 'confirmed')
                    <span class="badge bg-success">Paid ({{ strtoupper($payment->payment_method) }})</span>
                @elseif ($payment)
                    <span class="badge bg-warning text-dark">Payment {{ $payment->payment_status }}</span>
                @else
                    <span class="badge bg-secondary">Unpaid</span>
                @endif
            </p>
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

        {{-- Client: pay for the delivery if it has no confirmed payment yet --}}
        @if ($delivery->isOwnedByClient(Auth::user()) && $delivery->status !== 'canceled'
            && !($payment && $payment->payment_status === 'confirmed'))
            <a href="{{ route('payment.form', $delivery->id) }}" class="btn btn-warning mt-3 w-100">Pay for this delivery</a>
        @endif

        {{-- Driver: Start button if accepted --}}
        @if ($delivery->status === 'accepted' && $delivery->isAssignedToDriver(Auth::user()))
            <form method="POST" action="{{ route('driver.delivery.start', $delivery->id) }}" class="mt-3">
                @csrf
                <button class="btn btn-success w-100">Start Delivery</button>
            </form>
        @endif

        {{-- Driver: Complete button while in progress --}}
        @if ($delivery->status === 'in_progress' && $delivery->isAssignedToDriver(Auth::user()))
            <p class="text-muted small mt-3 mb-0">Your location is shared with the client while this page is open.</p>
            <form method="POST" action="{{ route('driver.delivery.complete', $delivery->id) }}" class="mt-2">
                @csrf
                <button class="btn btn-success w-100">Mark as Delivered</button>
            </form>
        @endif
    </div>
</section>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places"></script>

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

    fetch(`{{ url('/api/tracking/driver-location') }}/${driverId}`, { headers: { 'Accept': 'application/json' } })
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
    fetch(`{{ url('/api/tracking/driver-location') }}/${driverId}`, { headers: { 'Accept': 'application/json' } })
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

@if ($delivery->isAssignedToDriver(Auth::user()) && $delivery->status === 'in_progress')
navigator.geolocation.watchPosition(function(position) {
    fetch("{{ url('/api/tracking/update-location') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
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
