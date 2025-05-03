<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Passenger;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class DriverController extends Controller
{
    protected $agent;

    public function index()
    {
        //
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'vehicle_type' => 'required|string|max:255',
            'vehicle_brand' => 'nullable|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:255',
            'vehicle_year' => 'nullable|string|max:255',
            'plate_number' => 'required|string|max:255',
            'license_number' => 'required|string|max:255',
            'license_expiry' => 'nullable|date',
            'pricing_type' => 'required|in:fixed,per_km',
            'price_per_km' => 'nullable|numeric',
            'fixed_price' => 'nullable|numeric',
            'availability' => 'nullable|string',
            'earnings' => 'nullable|numeric',
            'delivery_count' => 'nullable|integer',
            'km_completed' => 'nullable|numeric',
            'rating_average' => 'nullable|numeric|min:0|max:5',
            'warning_count' => 'nullable|integer',
            'visibility_status' => 'required|string|in:visible,hidden',
            'registration_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
    
        // Assign static user_id (make sure this ID exists in the users table)
        $validatedData['user_id'] = 1; // Assuming the user ID is statically set for now
    
        // Handle file upload if any
        if ($request->hasFile('registration_document')) {
            $path = $request->file('registration_document')->store('documents', 'public');
            $validatedData['registration_document'] = $path;
        }
    
        // Create the driver profile with all the validated data
        Driver::create($validatedData);
    
        return redirect()->route('drivers.create')->with('success', 'Driver profile saved!');
    }

    public function show(Driver $driver)
    {
        //
    }

    public function showAll(Passenger $passengers)
    {
        $passengers = Passenger::paginate(7)->withQueryString();
        $this->agent = new Agent();

        return view('admin.table', [
            'title' => 'All Passengers Booking',
            "passengers" => $passengers,
            'agent' => $this->agent,
        ]);
    }

    public function showRecent(Passenger $Passengers)
    {
        $Passengers = Passenger::where('status', 'Unassigned')->orderBy('created_at', 'desc')->take(7)->paginate(7)->withQueryString();
        $this->agent = new Agent();

        return view('admin.table', [
            'title' => 'All Passengers Recent Booking',
            "passengers" => $Passengers,
            'agent' => $this->agent,
        ]);
    }

    public function showAvail(Passenger $Passengers)
    {
        $Passengers = Passenger::where('status', 'Unassigned')->paginate(7)->withQueryString();
        $this->agent = new Agent();

        return view('admin.table', [
            'title' => 'All Passengers Available Booking',
            "passengers" => $Passengers,
            'agent' => $this->agent,
        ]);
    }

    public function edit(Driver $driver)
    {
        //
    }

    public function update(Request $request, Driver $driver)
    {
        //
    }

    public function assign(Request $request)
    {
        Passenger::where('bookingRefNo', $request['bookingRefNo'])
            ->update([
                'status' => 'Assigned',
                'assignedBy' => auth()->user()->username,
            ]);

        $bookingRef = $request['bookingRefNo'];
        $driverName = auth()->user()->username;

        return redirect('/admin')->with('success', "Booking Reference $bookingRef <br> Has Been Assigned For $driverName");
    }

    public function assignBtn(Request $request)
    {
        $validated = $request->validate([
            'bookingInput' => 'required',
        ]);

        $exist = Passenger::select('bookingRefNo')
            ->where('bookingRefNo', $request->input('bookingInput'))
            ->first();

        if ($exist) {
            $isUnassigned = Passenger::select('bookingRefNo')
                ->where('bookingRefNo', $request->input('bookingInput'))
                ->where('status', 'Unassigned')
                ->first();

            if ($isUnassigned) {
                Passenger::where('bookingRefNo', $validated['bookingInput'])
                    ->update([
                        'status' => 'Assigned',
                        'assignedBy' => auth()->user()->username,
                    ]);

                return redirect('/admin')->with('success', 'Booking Has Been Assigned');
            }

            return redirect('/admin')->with('unassignedError', 'This Booking Has Been Assigned, Please Choose Another Passenger');
        }

        return redirect('/admin')->with('unassignedError', 'This Booking Number Did Not Exist');
    }

    public function searchBtn(Request $request)
    {
        $exist = Passenger::select('bookingRefNo')
            ->where('bookingRefNo', $request->input('bookingInput'))
            ->first();
        $this->agent = new Agent();

        if (!($request->input('bookingInput'))) {
            $Passengers = Passenger::where('status', 'Unassigned')
                ->orderBy('created_at', 'desc')
                ->take(7)
                ->paginate(7)
                ->withQueryString();

            return view('admin.table', [
                'title' => 'All Passengers Recent Booking',
                "passengers" => $Passengers,
                'agent' => $this->agent,
            ]);
        } else {
            if ($exist) {
                $Passengers = Passenger::where('bookingRefNo', $request->input('bookingInput'))
                    ->paginate(3)
                    ->withQueryString();

                return view('admin.table', [
                    'title' => 'All Passengers Recent Booking',
                    "passengers" => $Passengers,
                    'agent' => $this->agent,
                ]);
            } else {
                return redirect('/admin')->with('unassignedError', 'This Booking Number Did Not Exist');
            }
        }
    }

    public function destroy(Driver $driver)
    {
        //
    }
}
