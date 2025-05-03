@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Availability</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('driver.availability.create') }}" class="btn btn-primary mb-3">Add Availability</a>

    @if($availabilities->count())
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Driver ID</th> <!-- Displaying Driver ID -->
                <th>Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>City</th>
                <th>Governorate</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($availabilities as $availability)
                <tr>
                    <td>{{ $availability->driver_id }}</td> <!-- Showing Driver ID -->
                    <td>{{ $availability->day_of_week }}</td>
                    <td>{{ $availability->start_time }}</td>
                    <td>{{ $availability->end_time }}</td>
                    <td>{{ $availability->city }}</td>
                    <td>{{ $availability->governorate }}</td>
                    <td>
                        <a href="{{ route('driver.availability.edit', $availability->id) }}" class="btn btn-sm btn-warning">Edit</a>

                        <form action="{{ route('driver.availability.destroy', $availability->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this availability?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>You have not set any availability yet.</p>
    @endif
</div>
@endsection
