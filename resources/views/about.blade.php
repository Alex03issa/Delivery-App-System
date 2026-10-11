@extends('layouts.main')

@section('container')
    @include('partials.navbar')

    <!-- Start: About Section -->
    <section class="highlight-phone" style="background: rgb(254,251,240); height: 653px; padding-top: 113px;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <!-- Start: Intro -->
                    <div class="intro">
                        <h2>About Us</h2>
                        <p style="color: rgb(0,0,0);"><strong><em>Trusted Delivery Services Across the Nation</em></strong></p>
                        <p>
                            {{ config('app.name') }} is your trusted solution for fast, affordable, and secure delivery. 
                            We bridge the digital and physical worlds by instantly connecting individuals and businesses to reliable couriers.
                            Whether it’s packages, documents, or supplies — we move what matters to you, safely and on time.
                            <br><br>
                            We believe in smart logistics. That means real-time tracking, multiple payment options, and flexible scheduling, all at your fingertips. 
                            At {{ config('app.name') }}, our pursuit of innovation never stops — we’re constantly redefining how delivery should work.
                        </p>
                        @auth
                            @if(Auth::user()->role === 'client')
                                <a class="btn btn-primary" role="button" href="{{ route('delivery.create') }}" style="margin-left: -4px; background: rgb(59,59,59);">
                                    Request a Delivery
                                </a>
                            @endif
                        @else
                            <a class="btn btn-primary" role="button" href="/login" style="margin-left: -4px; background: rgb(59,59,59);">
                                Request a Delivery
                            </a>
                        @endauth

                    </div>
                    <!-- End: Intro -->
                </div>

                <div class="col-sm-4">
                    <!-- Start: Illustration or Phone Mockup -->
                    <div class="d-none d-md-block phone-mockup">
                        <img class="device" src="assets/img/car-2.jpg" alt="Delivery Vehicle">
                        <div class="screen"></div>
                    </div>
                    <!-- End: Illustration -->
                </div>
            </div>
        </div>
    </section>
    <!-- End: About Section -->

    <!-- How it works -->
    <section data-aos="zoom-in" data-aos-duration="1150" data-aos-once="true" class="py-5">
        <h3 id="how-it-works" class="text-center mb-4">How It Works</h3>
        <div class="container">
            <div class="row text-center">
                @foreach ([
                    ['fa-map-marker', 'Request', 'Pick the pickup and drop-off points on the map and describe the package.'],
                    ['fa-handshake-o', 'Driver accepts', 'A registered courier accepts the job from the available deliveries list.'],
                    ['fa-location-arrow', 'Live tracking', 'Follow the driver on the map with a live ETA and chat with them.'],
                    ['fa-credit-card', 'Pay your way', 'Card (Stripe), crypto (NOWPayments) or cash on delivery.'],
                ] as [$icon, $heading, $text])
                    <div class="col-sm-6 col-md-3 mb-3">
                        <i class="fa {{ $icon }} fa-2x mb-2" style="color: rgb(254,209,54);"></i>
                        <h5>{{ $heading }}</h5>
                        <p class="text-muted">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
