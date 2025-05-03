@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Delivery Tasks</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('driver.tasks.create') }}" class="btn btn-primary mb-3">Add New Delivery</a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Driver</th>
                <th>Client</th>
                <th>Package Details</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveries as $delivery)
                <tr>
                    <td>{{ $delivery->id }}</td>
                    <td>{{ $delivery->driver->name }}</td>
                    <td>{{ $delivery->client->id ?? 'N/A' }}</td>

                    <td>{{ $delivery->package_details }}</td>
                    <td>{{ $delivery->delivery_status }}</td>
                    <td>
                        <a href="{{ route('driver.tasks.show', $delivery->id) }}" class="btn btn-sm btn-primary">View / Update</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
