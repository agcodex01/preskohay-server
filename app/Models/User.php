<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Message;
use App\Models\Contact;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'contact_number',
        'profile_image',
        'first_name',
        'birthdate',
        'user_role',
        'last_name',
        'password',
        'address',
        'email',
        'age',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function messagesSend()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function application()
    {
        return $this->hasOne(Application::class);
    }

    public function contacts() {
        return $this->hasMany(Contact::class, 'sender_id');
    }

    public function searches()
    {
        return $this->hasMany(RecentSearch::class);
    }

    /**=========================================
     * METHODS
     *==========================================/

    /**
     * Check if user has made conversation
     */

    public function isHaveMadeConvo($receiver_id, $sender_id = null)
    {
        $sender_id = $sender_id ? $sender_id : $this->id;

        $messages = User::where('id', $sender_id)->with(
            [
                'messagesSend' => function ($q) use ($receiver_id, $sender_id) {
                    return $q->whereIn('receiver_id', [$receiver_id, $sender_id]);
                },
                'messagesReceived' => function ($q) use ($receiver_id, $sender_id) {
                    return $q->whereIn('sender_id', [$receiver_id, $sender_id]);
                }
            ]
        )->first();

        return count($messages['messagesSend']) > 0 && count($messages['messagesReceived']) > 0;
    }
}
