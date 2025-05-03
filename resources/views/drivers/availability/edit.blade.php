@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Availability</h2>

    <form action="{{ route('driver.availability.update', $availability->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Driver</label>
            <select name="driver_id" class="form-control">
                <option value="">Select Driver</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ $availability->driver_id == $driver->id ? 'selected' : '' }}>{{ $driver->id }}</option> <!-- Using driver ID -->
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Day of Week</label>
            <select name="day_of_week" class="form-control">
                @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                    <option value="{{ $day }}" {{ $availability->day_of_week == $day ? 'selected' : '' }}>{{ $day }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-2">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" value="{{ $availability->start_time }}">
        </div>

        <div class="form-group mt-2">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" value="{{ $availability->end_time }}">
        </div>

        <div class="form-group mt-2">
            <label>City</label>
            <input type="text" name="city" class="form-control" value="{{ $availability->city }}">
        </div>

        <div class="form-group mt-2">
            <label>Governorate</label>
            <input type="text" name="governorate" class="form-control" value="{{ $availability->governorate }}">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update</button>
        <a href="{{ route('driver.availability.index') }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>
@endsection
