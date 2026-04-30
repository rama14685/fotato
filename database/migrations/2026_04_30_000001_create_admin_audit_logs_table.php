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
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('restrict');
            $table->string('action_type'); // e.g., 'photographer_created', 'album_deleted', 'bulk_upload'
            $table->string('target_entity_type')->nullable(); // e.g., 'photographer', 'album', 'photo'
            $table->unsignedBigInteger('target_entity_id')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable(); // Store before/after values for modifications
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index('admin_id');
            $table->index('action_type');
            $table->index('target_entity_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
