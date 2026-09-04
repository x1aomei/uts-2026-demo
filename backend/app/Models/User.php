<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'google_id', 'avatar', 'password', 'phone', 'role', 'email_verified_at'
    ];

    protected $hidden = ['password', 'remember_token'];

    public function addresses() { return $this->hasMany(Address::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function cart() { return $this->hasOne(Cart::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function reviews() { return $this->hasMany(Review::class); }
}