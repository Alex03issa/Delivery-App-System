@extends('layouts.main')

@section('container')
    <div class="container mt-5">
        <h1 class="text-center" style="color: goldenrod;">Welcome to the Client Dashboard</h1>
        <p class="text-center">This is a placeholder dashboard. You can customize it later.</p>

        <div class="text-center mt-4">
            <a href="/" class="btn btn-secondary me-2">Back to Home</a>

            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>
@endsection
