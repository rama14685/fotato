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
    Schema::create('face_embeddings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('photo_id')->constrained('photos')->cascadeOnDelete();
        
        // Kita simpan vektor wajah dalam format teks panjang (JSON)
        // Nantinya Python akan mengirim deretan angka ke sini
        $table->text('embedding_vector'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_embeddings');
    }
};
