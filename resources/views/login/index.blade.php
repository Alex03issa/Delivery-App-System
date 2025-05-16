@extends('layouts.main')

@section('container')
    @include('partials.navbar')

    {{-- Success Message --}}
    @if (session()->has('success'))
        <script>
            Swal.fire(
                'Good job!',
                '{{ session('success') }}',
                'success'
            )
        </script>
    @endif

    {{-- Login Error --}}
    @if (session()->has('loginError'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('loginError') }}'
            })
        </script>
    @endif

    <!-- Start: Login Form -->
    <section class="login-clean" style="padding-top: 180px;">
        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="illustration">
                <h1 style="font-size: 30px; color: rgb(197,173,50);">Login</h1>
                <i class="la la-taxi" style="color: rgb(254,209,54);"></i>
            </div>

            <div class="mb-3">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    placeholder="Email" autofocus required value="{{ old('email') }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if (session()->has('success'))
                    <div class="alert alert-success text-center" role="alert">
                        {{ session('success') }}
                    </div>
                @endif


            </div>

            <div class="mb-3">
                <input type="password" name="password" placeholder="Password" class="form-control" required>
                <div class="text-end mt-1">
                    <a href="{{ route('password.request') }}" style="font-size: 13px;">Forgot Password?</a>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember Me (skip OTP next login)</label>
            </div>


            <div class="mb-3">
                <button class="btn btn-primary d-block w-100" type="submit"
                        style="background: rgb(254,209,54);">Log In</button>
            </div>

            <a class="already" href="{{ route('register') }}">Don't have an account? Register here.</a>

            {{-- Social Login Options --}}
            <div class="text-center mt-4">
                <p style="margin-bottom: 12px;">Or sign in with</p>
                <div class="d-flex justify-content-center">
                    <a href="{{ route('google.redirect') }}" class="btn me-2"
                       style="background-color: #db4437; border:none; color:white; width:200px;">
                        <i class="fab fa-google me-2"></i> Google
                    </a>
                    <a href="{{ route('facebook.redirect') }}" class="btn"
                       style="background-color: #3b5998; border:none; color:white; width:200px;">
                        <i class="fab fa-facebook-f me-2"></i> Facebook
                    </a>
                </div>
            </div>
        </form>
    </section>
    <!-- End: Login Form -->
@endsection
