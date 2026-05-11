<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * photo_faces stores one row per detected face per photo.
     * A single photo can have multiple rows (one per person detected).
     */
    public function up(): void
    {
        Schema::create('photo_faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained('photos')->cascadeOnDelete();

            // 128-dimensional face descriptor array stored as JSON
            $table->longText('face_descriptor');

            $table->timestamps();

            // Index for fast lookups when matching against a single album's photos
            $table->index('photo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_faces');
    }
};
