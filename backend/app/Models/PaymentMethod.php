<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'account_number',
        'account_holder',
        'instructions',
        'is_active',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}