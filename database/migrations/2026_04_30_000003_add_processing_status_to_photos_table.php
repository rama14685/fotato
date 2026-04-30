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
        Schema::table('photos', function (Blueprint $table) {
            if (!Schema::hasColumn('photos', 'processing_status')) {
                $table->enum('processing_status', ['uploaded', 'detecting_faces', 'generating_watermark', 'complete', 'failed'])
                    ->default('uploaded')
                    ->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            if (Schema::hasColumn('photos', 'processing_status')) {
                $table->dropColumn('processing_status');
            }
        });
    }
};
