@extends('layouts.main')

@section('container')
    @include('partials.navbar')

    <section class="login-clean" style="padding-top: 160px;">
        <div class="form-container">
            <div class="illustration text-center mb-4">
                <h1 style="font-size: 26px; color: rgb(197,173,50);">Forgot Password</h1>
                <i class="la la-key" style="color: rgb(254,209,54); font-size: 40px;"></i>
            </div>

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3">
                    <input class="form-control @error('email') is-invalid @enderror" type="email"
                        name="email" id="email" value="{{ old('email') }}" required
                        placeholder="Enter your email" autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if (session('success'))
                        <div class="valid-feedback d-block">{{ session('success') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary d-block w-100" type="submit"
                        style="background: rgb(254,209,54);">Send Reset Link</button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="already">Back to Login</a>
                </div>
            </form>

            {{-- Alerts --}}
            <div class="alert-container mt-4">
                @if (session('error'))
                    <div class="alert alert-danger"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
                @endif
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/ui_event_handlers.js') }}"></script>
@endsection
