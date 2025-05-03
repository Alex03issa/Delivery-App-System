<!-- resources/views/notifications/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center mb-4">Send FCM Notification</h2>

    <form action="{{ route('notifications.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="driver_id">Driver ID</label>
            <input type="number" class="form-control" id="driver_id" name="driver_id" required>
        </div>

        <div class="form-group">
            <label for="fcm_token">FCM Token</label>
            <input type="text" class="form-control" id="fcm_token" name="fcm_token" required>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block mt-4">Send Notification</button>
        </div>
    </form>
</div>
@endsection
