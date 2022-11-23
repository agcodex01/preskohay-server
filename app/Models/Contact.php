<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Contact extends Model
{
    use HasFactory;

    protected $fillable  = [
        'sender_id',
        'receiver_id'
    ];

    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public static function isAlreadyExist($receiver_id, $sender_id)
    {
        $contact = self::where('receiver_id', $receiver_id)->where('sender_id', $sender_id)->get();

        return $contact->count() > 0;
    }
}
