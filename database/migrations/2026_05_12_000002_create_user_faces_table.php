<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * user_faces stores the buyer's registered face descriptor (plain JSON).
     * One buyer can have one registered face (the most recent one wins via upsert).
     */
    public function up(): void
    {
        Schema::create('user_faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // 128-dimensional face descriptor array stored as plain JSON (not encrypted)
            $table->longText('face_descriptor');

            $table->timestamps();

            // One face registration per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_faces');
    }
};
