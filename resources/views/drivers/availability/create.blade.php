@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Set Availability</h2>

    <form action="{{ route('driver.availability.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Driver</label>
            <select name="driver_id" class="form-control">
                <option value="">Select Driver</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->id }}</option> <!-- Using driver ID -->
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Day of Week</label>
            <select name="day_of_week" class="form-control">
                @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-2">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control">
        </div>

        <div class="form-group mt-2">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control">
        </div>

        <div class="form-group mt-2">
            <label>City</label>
            <input type="text" name="city" class="form-control">
        </div>

        <div class="form-group mt-2">
            <label>Governorate</label>
            <input type="text" name="governorate" class="form-control">
        </div>

        <button type="submit" class="btn btn-success mt-3">Save</button>
    </form>
</div>
@endsection
