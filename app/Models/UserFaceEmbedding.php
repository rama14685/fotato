<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class UserFaceEmbedding extends Model
{
    protected $fillable = ['user_id', 'embedding_vector'];

    /**
     * Relationship to User model (belongsTo)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for decrypting embedding_vector
     * Returns the decrypted embedding as an array
     */
    public function getEmbeddingArrayAttribute()
    {
        return json_decode(Crypt::decryptString($this->embedding_vector), true);
    }

    /**
     * Mutator for encrypting embedding_vector
     * Accepts an array and stores it as encrypted JSON
     */
    public function setEmbeddingVectorAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['embedding_vector'] = Crypt::encryptString(json_encode($value));
        } else {
            $this->attributes['embedding_vector'] = $value;
        }
    }
}
