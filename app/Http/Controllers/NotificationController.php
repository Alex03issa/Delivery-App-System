<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FcmService;

class NotificationController extends Controller
{
    protected $fcmService;

    // Inject FCM service into the controller
    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    // Show the form to add FCM token for a driver
    public function index()
    {
        return view('notifications.index');
    }

    // Store the FCM token and send the notification
    public function storeFcmToken(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'fcm_token' => 'required|string',
        ]);

        // Find the driver by the ID from the form
        $driver = User::find($request->driver_id);

        if ($driver) {
            // Save the FCM token (store it in the remember_token field of the driver)
            $driver->remember_token = $request->fcm_token;
            $driver->save();

            // Send the notification
            $title = 'New Order Assigned!';
            $body = 'You have a new delivery request. Please check your dashboard for details.';
            $this->fcmService->sendPushNotification($driver->remember_token, $title, $body);

            return response()->json(['message' => 'FCM Token saved and notification sent!'], 200);
        }

        return response()->json(['message' => 'Driver not found'], 404);
    }
}
