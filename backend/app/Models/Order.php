<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference', 'retailer_id', 'wholesaler_id',
        'status', 'total_amount', 'note', 'delivered_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'delivered_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            $order->reference = 'VB-' . strtoupper(uniqid());
        });
    }

    public function retailer()
    {
        return $this->belongsTo(User::class, 'retailer_id');
    }

    public function wholesaler()
    {
        return $this->belongsTo(User::class, 'wholesaler_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
