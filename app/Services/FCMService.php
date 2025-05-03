<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FcmService
{
    // Firebase Cloud Messaging server key
    protected $fcmServerKey;

    public function __construct()
    {
        $this->fcmServerKey = env('FCM_SERVER_KEY');  // Use environment variable for FCM server key
    }

    // Send push notification
    public function sendPushNotification($fcmToken, $title, $body)
    {
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->fcmServerKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
        ]);

        return $response->json();
    }
}
