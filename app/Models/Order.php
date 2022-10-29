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
        'shipping_fee'
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
}
