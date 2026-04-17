<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'wholesaler_id', 'name', 'category', 'price',
        'unit', 'stock_qty', 'image_url', 'description', 'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function wholesaler()
    {
        return $this->belongsTo(User::class, 'wholesaler_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }
}
