<?php


namespace App\Http\Controllers;

use App\Models\Earning;
use App\Models\Driver;
use App\Models\Delivery;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    /**
     * Display a listing of earnings.
     */
    public function index()
    {
        $earnings = Earning::with(['driver', 'delivery'])->get();
        return view('drivers.earnings.index', compact('earnings')); // Updated view path
    }

    /**
     * Show the form for creating a new earning.
     */
    public function create()
    {
        $drivers = Driver::all(); // Get all drivers
        $deliveries = Delivery::all(); // Get all deliveries
    
        return view('drivers.earnings.create', compact('drivers', 'deliveries'));
    }

    /**
     * Store a newly created earning in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'delivery_id' => 'required|exists:deliveries,id',
            'total_revenue' => 'required|numeric',
            'commission' => 'required|numeric',
            'pending_payment' => 'required|numeric',
        ]);

        Earning::create($request->all());
        return redirect()->route('drivers.earnings.index')->with('success', 'Earning created successfully!');
    }

    /**
     * Display the specified earning.
     */
    public function show($id)
{
    $earning = Earning::findOrFail($id);

    return view('drivers.earnings.show', compact('earning'));
}

    /**
     * Show the form for editing the specified earning.
     */
    public function edit($id)
    {
        $earning = Earning::findOrFail($id);
        $drivers = Driver::all(); // Get all drivers
        $deliveries = Delivery::all(); // Get all deliveries
    
        return view('drivers.earnings.edit', compact('earning', 'drivers', 'deliveries'));
    }
    /**
     * Update the specified earning in storage.
     */
    public function update(Request $request, Earning $earning)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'delivery_id' => 'required|exists:deliveries,id',
            'total_revenue' => 'required|numeric',
            'commission' => 'required|numeric',
            'pending_payment' => 'required|numeric',
        ]);

        $earning->update($request->all());
        return redirect()->route('drivers.earnings.index')->with('success', 'Earning updated successfully!');
    }

    /**
     * Remove the specified earning from storage.
     */
    public function destroy(Earning $earning)
    {
        $earning->delete();
        return redirect()->route('drivers.earnings.index')->with('success', 'Earning deleted successfully!');
    }
}
