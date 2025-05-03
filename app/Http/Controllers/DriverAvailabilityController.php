<?php

namespace App\Http\Controllers;

use App\Models\DriverAvailability;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverAvailabilityController extends Controller
{
    public function index()
    {
        $availabilities = DriverAvailability::all();
        return view('drivers.availability.index', compact('availabilities'));
    }

    public function create()
    {
        $drivers = Driver::all(); // Fetch all drivers to select from
        return view('drivers.availability.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'city' => 'required',
            'governorate' => 'required',
        ]);

        DriverAvailability::create([
            'driver_id' => $request->driver_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'city' => $request->city,
            'governorate' => $request->governorate,
        ]);

        return redirect()->route('driver.availability.index')->with('success', 'Availability added.');
    }

    public function edit($id)
    {
        $availability = DriverAvailability::findOrFail($id);
        $drivers = Driver::all();
        return view('drivers.availability.edit', compact('availability', 'drivers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'city' => 'required',
            'governorate' => 'required',
        ]);

        $availability = DriverAvailability::findOrFail($id);
        $availability->update($request->all());

        return redirect()->route('driver.availability.index')->with('success', 'Availability updated.');
    }

    public function destroy($id)
    {
        $availability = DriverAvailability::findOrFail($id);
        $availability->delete();

        return redirect()->route('driver.availability.index')->with('success', 'Availability deleted.');
    }
}
