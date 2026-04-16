<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function rooms(Request $request)
    {
        $user = $request->user();

        $query = ChatRoom::with(['user', 'doctorProfile.user']);

        if ($user->hasRole('doctor')) {
            $query->where('doctor_profile_id', $user->doctorProfile->id);
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->orderBy('last_message_at', 'desc')->get());
    }

    public function messages(Request $request, $roomId)
    {
        $user = $request->user();
        $room = ChatRoom::findOrFail($roomId);

        // Security check
        if ($user->hasRole('doctor')) {
            if ($room->doctor_profile_id !== $user->doctorProfile->id)
                abort(403);
        } else {
            if ($room->user_id !== $user->id)
                abort(403);
        }

        $messages = $room->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        // Mark as read
        $room->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'doctor_profile_id' => 'required_without:chat_room_id',
            'chat_room_id' => 'required_without:doctor_profile_id',
            'content' => 'required|string',
        ]);

        $user = $request->user();

        if ($request->has('chat_room_id')) {
            $room = ChatRoom::findOrFail($request->chat_room_id);
        } else {
            // Find or create room (Patient initiating chat)
            $room = ChatRoom::firstOrCreate([
                'user_id' => $user->id,
                'doctor_profile_id' => $request->doctor_profile_id
            ]);
        }

        $message = Message::create([
            'chat_room_id' => $room->id,
            'sender_id' => $user->id,
            'content' => $request->input('content'),
        ]);

        $room->update([
            'last_message' => $request->input('content'),
            'last_message_at' => now()
        ]);

        return response()->json($message->load('sender'), 21);
    }
}
