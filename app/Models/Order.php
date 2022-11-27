<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes, Filterable;

    protected $fillable = [
        'total',
        'status',
        'shipping_fee',
        'driver_id',
        'drop_off'
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
        return $this->hasOne(User::class, 'id', 'driver_id');
    }
}
