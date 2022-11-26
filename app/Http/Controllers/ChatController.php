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
            return $q->with('receiver')->where('receiver_id', $request->receiver_id);
        }, 'messagesReceived' => function ($q) use ($request) {
            return $q->with('receiver')->where('sender_id', $request->receiver_id);
        }])->first();
    }

    public function sendMessage(Request $request)
    {

        $message = Message::create($request->all());
        $user = $request->user();

        // if ($user->isHaveMadeConvo($request->receiver_id, $request->sender_id)
        //     && !Contact::isAlreadyExist($request->receiver_id, $request->sender_id))
        // {
        //     Contact::create($request->all());
        // }
        if (!Contact::isAlreadyExist($request->receiver_id, $request->sender_id))
        {
            Contact::create($request->all());
        }

        try {
            broadcast(new MessageEvent($message))->toOthers();
        } catch (\Exception $e) {
            return $e;
        }

        return $message;
    }

    public function getContactList(Request $request)
    {
        $contacts = collect([...$request->user()->contacts_send, ...$request->user()->contacts_receive]);

        return $contacts->map(function ($contact) {
            $contact->receiverUser;
            $contact->senderUser;
            return $contact;
        });
    }
}
