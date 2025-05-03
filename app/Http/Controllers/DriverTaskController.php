<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\Client;
use App\Models\Driver;

class DriverTaskController extends Controller
{
    public function index()
    {
        // For testing, we can show all deliveries
        $deliveries = Delivery::with(['driver', 'client'])->get();

        return view('drivers.tasks.index', compact('deliveries'));
    }

    public function show($id)
    {
        $delivery = Delivery::findOrFail($id);

        return view('drivers.tasks.show', compact('delivery'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'delivery_status' => 'required|in:Accepted,InProgress,Delivered,Canceled',
        ]);

        $delivery = Delivery::findOrFail($id);
        $delivery->delivery_status = $request->input('delivery_status');
        $delivery->save();

        return redirect()->route('driver.tasks.index')->with('success', 'Delivery status updated.');
    }

    public function create()
    {
        $clients = Client::all();
        $drivers = Driver::all(); // Optional if you want dynamic driver dropdown

        return view('drivers.tasks.create', compact('clients', 'drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'client_id' => 'required|exists:clients,id',
            'package_details' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'total_price' => 'required|numeric',
        ]);

        $delivery = new Delivery();
        $delivery->driver_id = $request->input('driver_id');
        $delivery->client_id = $request->input('client_id');
        $delivery->package_details = $request->input('package_details');
        $delivery->delivery_date = $request->input('delivery_date');
        $delivery->total_price = $request->input('total_price');
        $delivery->delivery_status = 'Pending';
        $delivery->save();

        return redirect()->route('driver.tasks.index')->with('success', 'New delivery added.');
    }
}
