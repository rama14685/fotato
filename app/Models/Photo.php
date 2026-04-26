<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = ['album_id', 'original_path', 'watermark_path', 'price'];

    // Relasi balik ke Album
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    // Relasi ke Face Embedding (1 foto punya 1 data vektor wajah)
    public function faceEmbedding()
    {
        return $this->hasOne(FaceEmbedding::class);
    }

    // Relasi ke Transaction Items (1 foto bisa di-beli di banyak transaction)
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}