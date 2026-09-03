<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'price', 'stock', 'image'];

    // Relasi: Produk milik 1 kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi: Produk bisa ada di banyak keranjang
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Relasi: Produk bisa ada di banyak detail pesanan
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi: Produk bisa ada di banyak wishlist user
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}