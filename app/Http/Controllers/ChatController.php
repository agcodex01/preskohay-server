<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageEvent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function fetchMessage()
    {
        return Message::with('user')->get();
    }

    public function sendMessage(Request $request)
    {
        $message = Message::create([
            'message' => $request->message,
            'user_id' => $request->user()->id
        ]);

        event(new MessageEvent($request->user_id, $request->message));

        return $message;
    }
}
