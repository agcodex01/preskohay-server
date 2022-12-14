<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'license_img',
        'motor_img'
    ];


    public function user()
    {
        return $this->belongTo(User::class);
    }
}
