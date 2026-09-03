<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi: User memiliki banyak item keranjang
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Relasi: User memiliki banyak riwayat transaksi
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Relasi: User memiliki banyak produk di wishlist
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}