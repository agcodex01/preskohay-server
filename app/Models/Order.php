<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'total',
        'status',
        'shipping_fee',
        'driver_id'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot([
                'subtotal',
                'quantity'
            ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->hasOne(User::class, 'driver_id', 'id');
    }
}
