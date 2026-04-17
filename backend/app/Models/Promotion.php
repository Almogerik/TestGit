<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'wholesaler_id', 'product_id', 'title', 'description',
        'discount_percent', 'promo_price', 'starts_at', 'ends_at', 'is_active',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'discount_percent' => 'decimal:2',
        'promo_price'      => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    public function wholesaler()
    {
        return $this->belongsTo(User::class, 'wholesaler_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
