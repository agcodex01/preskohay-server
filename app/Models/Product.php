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
        'unit',
        'image',
        'stocks',
        'post_id',
        'category',
        'description',
        'price_per_unit'
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }
}
