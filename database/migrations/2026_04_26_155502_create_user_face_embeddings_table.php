<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_face_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Store encrypted 128-dimensional face embedding as JSON text
            // This is encrypted using Laravel's Crypt facade before storage
            $table->text('embedding_vector');
            
            $table->timestamps();
            
            // Add index on user_id for fast lookups
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_face_embeddings');
    }
};
