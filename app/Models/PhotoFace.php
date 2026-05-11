<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoFace extends Model
{
    protected $fillable = ['photo_id', 'face_descriptor'];

    protected $casts = [
        'face_descriptor' => 'array',
    ];

    /**
     * Belongs to a Photo (many-to-one).
     * One photo can have multiple PhotoFace rows.
     */
    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }
}
