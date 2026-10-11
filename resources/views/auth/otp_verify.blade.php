@extends('layouts.main')

@section('container')
<section class="login-clean">
    <form action="{{ route('otp.verify.submit') }}" method="POST">
        @csrf
        <h2 class="text-center">OTP Verification</h2>

        @if(session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
        @endif

        <div class="mb-3">
            <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>
        </div>

        <div class="mb-3">
            <button class="btn btn-primary d-block w-100" type="submit">Verify</button>
        </div>
    </form>
</section>
@endsection
