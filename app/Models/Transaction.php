<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['buyer_id', 'photographer_id', 'total_amount', 'status'];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    // Relasi ke buyer
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // Relasi ke photographer
    public function photographer()
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    // Relasi ke transaction items
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
