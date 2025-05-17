@extends('layouts.main')

@section('container')
<div class="container mt-5">
    <h3>Available Deliveries</h3>

    @if ($deliveries->isEmpty())
        <div class="alert alert-info">No pending deliveries available right now.</div>
    @else
        <div class="accordion" id="deliveryAccordion">
            @foreach ($deliveries as $delivery)
                @php
                    $pickup = $delivery->pickupLocation;
                    $dropoff = $delivery->dropoffLocation;
                @endphp

                <div class="accordion-item mb-2">
                    <h2 class="accordion-header" id="heading{{ $delivery->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse{{ $delivery->id }}" aria-expanded="false"
                                aria-controls="collapse{{ $delivery->id }}">
                            Delivery #{{ $delivery->id }} — {{ $pickup->address ?? 'N/A' }} ➝ {{ $dropoff->address ?? 'N/A' }}
                        </button>
                    </h2>
                    <div id="collapse{{ $delivery->id }}" class="accordion-collapse collapse"
                         aria-labelledby="heading{{ $delivery->id }}" data-bs-parent="#deliveryAccordion">
                        <div class="accordion-body">
                            <strong>Pickup:</strong> {{ $pickup->address ?? 'N/A' }}<br>
                            <strong>Dropoff:</strong> {{ $dropoff->address ?? 'N/A' }}<br><br>

                            <strong>Package:</strong><br>
                            Size: {{ ucfirst($delivery->package_size) }}<br>
                            Weight: {{ $delivery->package_weight }} kg<br>
                            Dimensions: {{ $delivery->length_cm }} x {{ $delivery->width_cm }} x {{ $delivery->height_cm }} cm<br>
                            Volume: {{ $delivery->package_volume }} cm³<br><br>

                            <strong>Pricing:</strong><br>
                            Price: ${{ $delivery->price }}<br>
                            Extra Charge: ${{ $delivery->extra_charge }}<br><br>

                            <strong>Urgency:</strong> {{ ucfirst($delivery->urgency_level) }}<br>
                            <strong>Scheduled for:</strong> {{ $delivery->delivery_date->format('Y-m-d H:i') }}<br>
                            @if($delivery->note)
                                <strong>Note:</strong> {{ $delivery->note }}<br>
                            @endif

                            <form method="POST" action="{{ route('driver.accept', $delivery->id) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">Accept Delivery</button>
                            </form>

                            <div id="map-{{ $delivery->id }}" style="height: 300px; border-radius: 8px; border: 1px solid #ccc;" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD900K9mRZHe8UMJdJL4u8CaBtJGonpvzo"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @foreach ($deliveries as $delivery)
            @php
                $pickup = $delivery->pickupLocation;
                $dropoff = $delivery->dropoffLocation;
            @endphp

            @if ($pickup && $dropoff)
                const map{{ $delivery->id }} = new google.maps.Map(document.getElementById("map-{{ $delivery->id }}"), {
                    center: { lat: {{ $pickup->lat }}, lng: {{ $pickup->lng }} },
                    zoom: 13,
                });

                new google.maps.Marker({
                    position: { lat: {{ $pickup->lat }}, lng: {{ $pickup->lng }} },
                    map: map{{ $delivery->id }},
                    title: "Pickup Location",
                    icon: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
                });

                new google.maps.Marker({
                    position: { lat: {{ $dropoff->lat }}, lng: {{ $dropoff->lng }} },
                    map: map{{ $delivery->id }},
                    title: "Dropoff Location",
                    icon: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
                });
            @endif
        @endforeach
    });
</script>
@endsection
