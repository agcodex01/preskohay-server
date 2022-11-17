<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Events\MessageEvent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function fetchMessages(Request $request)
    {
        return User::where('id', $request->user()->id)->with(['messagesSend' => function ($q) use ($request) {
            return $q->where('receiver_id', $request->receiver_id);
        }, 'messagesReceived' => function ($q) use ($request) {
            return $q->where('sender_id', $request->receiver_id);
        }])->get();
    }

    public function sendMessage(Request $request)
    {
        $message = Message::create($request->all());
        $user = $request->user();

        broadcast(new MessageEvent($request->message, $user))->toOthers();

        return $message;
    }
}
