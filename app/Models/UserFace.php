<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFace extends Model
{
    protected $fillable = ['user_id', 'face_descriptor'];

    protected $casts = [
        'face_descriptor' => 'array',
    ];

    /**
     * Belongs to a User (one registered face per buyer).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
