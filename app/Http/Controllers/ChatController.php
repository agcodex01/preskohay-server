<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Contact;
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
        }])->first();
    }

    public function sendMessage(Request $request)
    {
        
        $message = Message::create($request->all());
        $user = $request->user();

        if ($user->isHaveMadeConvo($request->receiver_id, $request->sender_id) 
            && !Contact::isAlreadyExist($request->receiver_id, $request->sender_id))
        {
            Contact::create($request->all());
        }

        broadcast(new MessageEvent($message))->toOthers();

        return $message;
    }

    public function getContactList(Request $request)
    {
        $contacts = $request->user()->contacts;
        
        return $contacts->map(function ($contact) {
            $contact->receiverUser;
            return $contact;
        });
    }
}
