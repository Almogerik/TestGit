<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'phone', 'email', 'password',
        'role', 'store_name', 'address', 'city',
        'is_active', 'otp_code', 'otp_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'otp_code', 'otp_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at'    => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'wholesaler_id');
    }

    public function ordersAsWholesaler()
    {
        return $this->hasMany(Order::class, 'wholesaler_id');
    }

    public function ordersAsRetailer()
    {
        return $this->hasMany(Order::class, 'retailer_id');
    }

    public function wholesalers()
    {
        return $this->belongsToMany(User::class, 'retailer_wholesaler', 'retailer_id', 'wholesaler_id')
            ->withPivot('status')->withTimestamps();
    }

    public function retailers()
    {
        return $this->belongsToMany(User::class, 'retailer_wholesaler', 'wholesaler_id', 'retailer_id')
            ->withPivot('status')->withTimestamps();
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'wholesaler_id');
    }
}
