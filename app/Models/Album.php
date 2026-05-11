<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;
    protected $fillable = ['photographer_id', 'title', 'location', 'event_date', 'thumbnail_path'];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    // Relasi balik ke User (Fotografer)
    public function photographer()
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    // Relasi ke banyak Foto
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    // Helper: dapatkan semua transactions dari photos dalam album ini
    public function transactions()
    {
        return Transaction::whereHas('items.photo', function($q) {
            $q->whereIn('photo_id', $this->photos()->pluck('id'));
        });
    }
}