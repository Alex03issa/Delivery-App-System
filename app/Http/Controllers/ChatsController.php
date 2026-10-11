<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\DeliveryRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatsController extends Controller
{
    /**
     * User ids the current user may chat with: a client can talk to the
     * drivers of their deliveries, a driver to the clients of theirs.
     */
    private function contactIds(User $auth): array
    {
        if ($auth->role === 'client' && $auth->client) {
            return DeliveryRequest::where('delivery_requests.client_id', $auth->client->id)
                ->join('drivers', 'drivers.id', '=', 'delivery_requests.driver_id')
                ->distinct()
                ->pluck('drivers.user_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if ($auth->role === 'driver' && $auth->driver) {
            return DeliveryRequest::where('delivery_requests.driver_id', $auth->driver->id)
                ->join('clients', 'clients.id', '=', 'delivery_requests.client_id')
                ->distinct()
                ->pluck('clients.user_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return [];
    }

    private function ensureContact(User $auth, $userId): int
    {
        $userId = (int) $userId;
        abort_unless($userId && in_array($userId, $this->contactIds($auth), true), 403);

        return $userId;
    }

    public function index(Request $request)
    {
        $auth = Auth::user();
        $contactIds = $this->contactIds($auth);

        $users = User::whereIn('id', $contactIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Only preselect a conversation the user is allowed to have.
        $selectedUserId = (int) $request->query('with');
        if (!in_array($selectedUserId, $contactIds, true)) {
            $selectedUserId = null;
        }

        return view('chat.chat-view', [
            'users' => $users,
            'selectedUserId' => $selectedUserId,
            'title' => 'Chat',
        ]);
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'user' => 'required|integer',
            'message' => 'required|string|max:2000',
        ]);

        $auth = Auth::user();
        $to = $this->ensureContact($auth, $data['user']);

        $item = new Chat();
        $item->date_time = now();
        $item->send_by = $auth->id;
        $item->send_to = $to;
        $item->message_type = 'text';
        $item->message = e($data['message']); // stored escaped, rendered as HTML by the chat view
        $item->save();

        return $item;
    }

    public function getChatHistory(Request $request)
    {
        $auth = Auth::user();
        $other = $this->ensureContact($auth, $request->query('userID'));

        $messages = Chat::with('sender:id,name')
            ->where(function ($query) use ($auth, $other) {
                $query->where(function ($q) use ($auth, $other) {
                    $q->where('send_by', $auth->id)->where('send_to', $other);
                })->orWhere(function ($q) use ($auth, $other) {
                    $q->where('send_by', $other)->where('send_to', $auth->id);
                });
            })
            ->orderBy('date_time', 'asc')
            ->get();

        Chat::where('send_by', $other)
            ->where('send_to', $auth->id)
            ->where('is_received', 0)
            ->update(['is_received' => 1]);

        return $messages;
    }

    /**
     * Server-sent event with the next unread message from $user_id (the
     * browser's EventSource reconnects automatically to poll again).
     */
    public function getNewMessages($user_id)
    {
        $auth = Auth::user();
        $other = $this->ensureContact($auth, $user_id);

        $message = Chat::where('send_to', $auth->id)
            ->where('send_by', $other)
            ->where('is_received', 0)
            ->orderBy('date_time', 'asc')
            ->with('sender:id,name')
            ->first();

        if ($message) {
            $message->is_received = 1;
            $message->save();
        }

        $body = $message
            ? 'data:' . json_encode(['item' => $message]) . "\n\n"
            : "retry: 3000\n\n";

        return response($body, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
