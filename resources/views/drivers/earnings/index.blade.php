@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Earnings</h1>
    <a href="{{ route('drivers.earnings.create') }}" class="btn btn-primary mb-3">Create Earning</a>
    <table class="table">
        <thead>
            <tr>
                <th>Driver Plate Number</th>
                <th>Delivery</th>
                <th>Total Revenue</th>
                <th>Commission</th>
                <th>Pending Payment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($earnings as $earning)
                <tr>
                    <!-- Display driver plate number -->
                    <td>{{ $earning->driver->plate_number }}</td>
                    <td>{{ $earning->delivery->package_details }}</td>
                    <td>{{ $earning->total_revenue }}</td>
                    <td>{{ $earning->commission }}</td>
                    <td>{{ $earning->pending_payment }}</td>
                    <td>
                        <a href="{{ route('drivers.earnings.show', $earning->id) }}" class="btn btn-info">View</a>
                        <a href="{{ route('drivers.earnings.edit', $earning->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('drivers.earnings.destroy', $earning->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
