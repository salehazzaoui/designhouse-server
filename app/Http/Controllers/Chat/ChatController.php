<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient' => ['required'],
            'body' => ['required']
        ]);
        $recipient = $request->recipient;
        $user = $request->user();
        $body = $request->body;

        // chech if there is existing chat between auth user and recipient
        $chat = $user->chats()->with(['messages', 'participants'])->get();
        if (!$chat === false) {
            $chat = Chat::create([]);
            $chat->participants()->sync([$user->id, $recipient]);
        }
        // add the message to the chat
        $message = Message::create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'body' => $body,
            'last_read' => null
        ]);

        return new MessageResource($message);
    }

    public function getUserChats(Request $request)
    {
        $chats = $request->user()->chats()->with(['messages', 'participants'])->get();
        return ChatResource::collection($chats);
    }

    public function getChatMessages($id)
    {
        $messages = Message::withTrashed()->where('chat_id', $id)->get();
        return MessageResource::collection($messages);
    }

    public function markAsRead($id)
    {
        $chat = Chat::findOrFail($id);
        $chat->markAsReadForUser(auth()->id());
        return response()->json(['message' => 'successful'], 200);
    }

    public function destroyMessage($id)
    {
        $message = Message::findOrFail($id);
        $this->authorize('delete', $message);
        $message->delete();
        return response()->json(['message' => 'message deleted'], 200);
    }
}
