<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceEmbedding extends Model
{
    protected $fillable = ['photo_id', 'embedding_vector'];

    // Relasi balik ke Foto
    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }
}