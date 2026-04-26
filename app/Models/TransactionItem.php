<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = ['transaction_id', 'photo_id', 'price', 'quantity'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relasi ke transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke photo
    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }
}
