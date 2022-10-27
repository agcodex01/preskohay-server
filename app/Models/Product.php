<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'price_per_unit',
        'unit',
        'description',
        'stocks'
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }
}
