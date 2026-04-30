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
        Schema::table('users', function (Blueprint $table) {
            // Add nullable face_embedding_id column
            // Nullable to support photographers who don't need face scan
            $table->unsignedBigInteger('face_embedding_id')->nullable()->after('wallet_balance');
            
            // Add foreign key constraint to user_face_embeddings.id
            $table->foreign('face_embedding_id')
                  ->references('id')
                  ->on('user_face_embeddings')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['face_embedding_id']);
            $table->dropColumn('face_embedding_id');
        });
    }
};
