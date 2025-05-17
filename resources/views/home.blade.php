@extends('layouts.main')

@section('container')
    @include('partials.homeHeader')
  

    <!-- About / How it works -->
    <section id="about" style="margin-top: -75px;">
        <div class="container">
            <div class="row row-about">
                <div class="col-lg-12 text-center" data-aos="zoom-in" data-aos-duration="500" data-aos-once="true">
                    <h3 class="text-muted section-subheading">
                        <i class="fa fa-dot-circle-o" style="color: rgb(254,209,54);"></i><br>
                        <strong>Cabs Online Services</strong><br>
                    </h3>
                    <div id="div-about" class="text-center">
                        <h2 class="text-uppercase"><strong>HOW IT WORKS</strong></h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <ul class="list-group timeline">
                        <li class="list-group-item" data-aos="zoom-in" data-aos-duration="1000" data-aos-once="true">
                            <div class="timeline-image">
                                <img class="rounded-circle img-fluid" src="assets/img/about/tap.png">
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h4 class="subheading"><strong>Request A Delivery</strong></h4>
                                </div>
                                <div class="timeline-body">
                                    <p class="text-muted">Click "Request Delivery", enter pickup/drop-off and package details — it only takes a minute!</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item timeline-inverted" data-aos="zoom-in" data-aos-duration="1000" data-aos-once="true">
                            <div class="timeline-image">
                                <img class="rounded-circle img-fluid" src="assets/img/about/taxi-driver.png">
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h4 class="subheading"><strong>Assign A Courier</strong></h4>
                                </div>
                                <div class="timeline-body">
                                    <p class="text-muted">We auto-assign an available courier near you, or you can choose one manually.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item" data-aos="zoom-in" data-aos-duration="1000" data-aos-once="true">
                            <div class="timeline-image">
                                <img class="rounded-circle img-fluid" src="assets/img/about/car.png">
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h4 class="subheading"><strong>Track Your Package</strong></h4>
                                </div>
                                <div class="timeline-body">
                                    <p class="text-muted">Follow your delivery in real-time, receive updates, and know exactly when it arrives.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item timeline-inverted" data-aos="zoom-in" data-aos-duration="1000" data-aos-once="true">
                            <div class="timeline-image">
                                <img class="rounded-circle img-fluid" src="assets/img/about/arrived.png">
                            </div>
                            <div class="timeline-panel">
                                <div class="timeline-heading">
                                    <h4 class="subheading"><strong>Package Delivered</strong></h4>
                                </div>
                                <div class="timeline-body">
                                    <p class="text-muted">Your package is delivered safely and confirmed by the recipient — simple and secure.</p>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item timeline-inverted" data-aos="zoom-in" data-aos-duration="1000" data-aos-once="true">
                            <div class="timeline-image">
                                <h4>Be Part<br>&nbsp;Of This<br>Journey!</h4>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Delivery Request Form -->
    <section class="highlight-phone" style="background: rgb(255,192,0);">
        <div id="booking-cta" class="container text-center">
            <h3>Request A Delivery</h3>
            <form class="row g-3" method="POST" action="continue-booking">
                @csrf
                <div class="mb-3">
                   
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="sbname" placeholder="🏡 Pickup Address">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="dsbname" placeholder="🏡 Drop-off Address">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="phone" placeholder="☎️ Phone Number">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="pickUpDate">
                </div>
                <div class="col-12">
                    <input class="btn btn-dark btn-lg" type="submit" style="border-radius: 40px;" value="Request Delivery">
                </div>
            </form>
        </div>
    </section>

    <!-- About Us -->
    <section class="highlight-phone" style="background: rgb(254,251,240);">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="intro">
                        <div class="div-title"><h2>About us</h2></div>
                        <p style="color: rgb(0,0,0);"><strong><em>Trusted Delivery Services Across All Cities</em></strong></p>
                        <p>
                            Cabs Online is your reliable delivery platform connecting clients with independent couriers.
                            From small parcels to urgent packages, we make delivery fast, secure, and transparent with live tracking and real-time updates.
                        </p>
                        <a class="btn btn-primary" href="/booking" style="background: rgb(59,59,59);">Request A Delivery</a>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="d-none d-md-block phone-mockup taxi-about-img">
                        <img class="device" src="assets/img/car-3.jpg">
                        <div class="screen"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Media Logos -->
    <section data-aos="zoom-in" data-aos-duration="1150" data-aos-once="true" class="py-5">
        <h3 id="seen" class="text-center">As Seen On</h3>
        <div class="container">
            <div class="row">
                @foreach (['google', 'facebook', 'airbnb', 'netflix'] as $logo)
                    <div class="col-sm-6 col-md-3">
                        <a href="#"><img class="img-fluid d-block mx-auto" src="assets/img/clients/{{ $logo }}.jpg"></a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Join the Team -->
    <section class="highlight-phone" style="background: rgb(255,192,0);">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="intro">
                        <h5 style="color: rgb(0,0,0);">Join The Team</h5>
                        <h2><strong>Become Our Courier — Work On Your Terms!</strong></h2>
                    </div>
                </div>
                <div class="col-sm-4">
                    <a class="btn btn-lg btn-dark driver-btn" href="/register">Become A Courier</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section id="services" style="padding-top: 90px; background: #111; color: #fff;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center" style="margin-top: -20px;">
                    <h3 class="text-muted section-subheading">
                        <i class="fa fa-dot-circle-o" style="color: rgb(254,209,54);"></i><br><strong>Cabs Online Benefit List</strong>
                    </h3>
                    <h2 class="text-uppercase section-heading benefit-space">Why choose us</h2>
                </div>
            </div>
            <div class="row text-center align-up">
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fa fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-shield-alt fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="section-heading">Safe & Secure</h4>
                    <p class="text-muted">Live tracking, verified couriers, and full delivery confirmation give you peace of mind.</p>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fa fa-circle fa-stack-2x text-primary"></i>
                        <i class="fa fa-comments fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="section-heading">Instant Support</h4>
                    <p class="text-muted">Chat with your assigned courier anytime during delivery. We’re always here to help.</p>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fa fa-circle fa-stack-2x text-primary"></i>
                        <i class="fa fa-credit-card fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="section-heading">Flexible Payments</h4>
                    <p class="text-muted">Pay online, by crypto, or cash on delivery — your choice, your convenience.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
