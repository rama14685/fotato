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

    // Relasi ke Face Embedding (1 foto punya 1 data vektor wajah) - LEGACY
    public function faceEmbedding()
    {
        return $this->hasOne(FaceEmbedding::class);
    }

    // Relasi ke PhotoFace (1 foto bisa punya banyak wajah terdeteksi)
    public function photoFaces()
    {
        return $this->hasMany(PhotoFace::class);
    }

    // Relasi ke Transaction Items (1 foto bisa di-beli di banyak transaction)
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // Helper: Cek apakah foto sudah dibeli oleh user tertentu
    public function isPurchasedBy($userId)
    {
        if (!$userId) {
            return false;
        }

        return $this->transactionItems()
            ->whereHas('transaction', function($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->where('status', 'completed');
            })
            ->exists();
    }

    // Helper: Get display path (watermark jika belum dibeli, original jika sudah dibeli)
    public function getDisplayPath($userId = null)
    {
        // Jika sudah dibeli, tampilkan original (tanpa watermark)
        if ($userId && $this->isPurchasedBy($userId)) {
            return $this->original_path;
        }

        // Jika belum dibeli, tampilkan watermark (atau original jika watermark belum ada)
        return $this->watermark_path ?: $this->original_path;
    }
}