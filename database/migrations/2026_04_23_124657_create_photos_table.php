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
    Schema::create('photos', function (Blueprint $table) {
        $table->id();
        // Relasi ke tabel albums
        $table->foreignId('album_id')->constrained('albums')->cascadeOnDelete();
        
        $table->string('original_path'); // Path penyimpanan foto HD (Asli)
        $table->string('watermark_path'); // Path penyimpanan foto ber-watermark (Preview)
        $table->decimal('price', 10, 2)->default(0); // Harga foto
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
