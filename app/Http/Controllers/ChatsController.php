<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\DeliveryRequest;

class ChatsController extends Controller
{

    public function index(Request $request)
    {
        $auth = Auth::user();
        $role = $auth->role;
        $selectedUserId = $request->query('with');
        $users = collect();
    
        if ($role === 'client') {
            $driverIds = DeliveryRequest::where('client_id', $auth->id)
                ->whereNotNull('driver_id')
                ->pluck('driver_id')
                ->unique();
    
            $users = User::whereIn('id', function ($query) use ($driverIds) {
                    $query->select('user_id')
                          ->from('drivers')
                          ->whereIn('id', $driverIds);
                })
                ->select('id', 'name')
                ->get();
    
        } elseif ($role === 'driver') {
            $driver = $auth->driver;
            if ($driver) {
                $clientIds = DeliveryRequest::where('driver_id', $driver->id)
                    ->pluck('client_id')
                    ->unique();
    
                $users = User::whereIn('id', $clientIds)
                    ->select('id', 'name')
                    ->get();
            }
        }
    
        if ($selectedUserId) {
            $userIds = $users->pluck('id')->toArray();
            $userIds[] = (int) $selectedUserId;
    
            $users = User::whereIn('id', array_unique($userIds))
                ->select('id', 'name')
                ->get();
        }
    
        return view('chat.chat-view', [
            'users' => $users,
            'selectedUserId' => $selectedUserId,
            'title' => 'Chat'
        ]);
    }
    
    

    public function sendMessage(Request $request)
    {
        $item = new Chat();
        $item->date_time = now();
        $item->send_by = Auth::user()->id;
        $item->send_to = $request->user;
        $item->message_type = 'text';
        $item->message = e($request->input('message')); // Use the e helper
        $item->save();

        return $item;
    }

    public function getChatHistory(Request $request)
    {
        $messages = Chat::with('sender')
            ->where(function ($query) use ($request) {
                $query->where('send_by', Auth::user()->id)
                    ->where('send_to', $request->userID);
            })
            ->orWhere(function ($query) use ($request) {
                $query->where('send_by', $request->userID)
                    ->where('send_to', Auth::user()->id);
            })
            ->orderBy('date_time', 'asc')
            ->get();

        foreach ($messages->where('send_to',Auth::user()->id) as $message) {
            $message->is_received = 1;
            $message->update();
        }
        return $messages;
    }



    public function getNewMessages($user_id)
    {
        $message = Chat::where('send_to', Auth::id())
            ->where('send_by', $user_id)
            ->where('is_received', 0)
            ->orderBy('date_time', 'asc')
            ->with('sender')
            ->first();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        if ($message) {
            echo "data:" . json_encode(['item' => $message]) . "\n\n";
            $message->is_received = 1;
            $message->save(); 
        } else {
            echo "\n\n";
        }

        ob_flush();
        flush();
    }

}
