<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',             // Tambahan baru
        'wallet_balance',   // Tambahan baru
        'status',           // active atau inactive
        'face_embedding_id', // For face scan registration
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke Album (Satu user/fotografer punya banyak album)
     */
    public function albums()
    {
        return $this->hasMany(Album::class, 'photographer_id');
    }

    /**
     * Relasi ke Transaction sebagai photographer
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'photographer_id');
    }

    /**
     * Relasi ke Transaction sebagai buyer
     */
    public function purchases()
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    /**
     * Relationship to UserFaceEmbedding (hasOne) - LEGACY
     */
    public function faceEmbedding()
    {
        return $this->hasOne(UserFaceEmbedding::class, 'id', 'face_embedding_id');
    }

    /**
     * Relationship to UserFace (new table, plain JSON descriptor)
     */
    public function userFace()
    {
        return $this->hasOne(UserFace::class);
    }
}