<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add explicit index on photos.album_id for performance.
 *
 * The original photos migration uses foreignId('album_id')->constrained(),
 * which creates a foreign key constraint. In MySQL, foreign key constraints
 * automatically create an index. However, to ensure the index is explicitly
 * present across all database drivers (including SQLite) and to document
 * the performance intent, this migration adds the index explicitly if it
 * does not already exist.
 *
 * Requirement 9.1: The System SHALL use database indexes on album_id for
 * efficient photo retrieval.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the index already exists before adding it.
        // On MySQL, the foreign key constraint creates an index named 'photos_album_id_foreign'
        // or 'photos_album_id_index'. We add it only if absent to avoid duplicate index errors.
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: check existing indexes via PRAGMA
            $indexes = DB::select("PRAGMA index_list('photos')");
            $indexNames = array_column($indexes, 'name');

            $hasIndex = false;
            foreach ($indexNames as $name) {
                if (str_contains($name, 'album_id')) {
                    $hasIndex = true;
                    break;
                }
            }

            if (!$hasIndex) {
                Schema::table('photos', function (Blueprint $table) {
                    $table->index('album_id', 'photos_album_id_index');
                });
            }
        } else {
            // MySQL / MariaDB / PostgreSQL: use Schema::hasIndex if available,
            // otherwise wrap in a try/catch to handle duplicate index gracefully.
            try {
                Schema::table('photos', function (Blueprint $table) {
                    $table->index('album_id', 'photos_album_id_index');
                });
            } catch (\Exception $e) {
                // Index already exists (e.g., created by foreign key constraint on MySQL).
                // This is expected and not an error.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('photos')");
            $indexNames = array_column($indexes, 'name');

            if (in_array('photos_album_id_index', $indexNames)) {
                Schema::table('photos', function (Blueprint $table) {
                    $table->dropIndex('photos_album_id_index');
                });
            }
        } else {
            try {
                Schema::table('photos', function (Blueprint $table) {
                    $table->dropIndex('photos_album_id_index');
                });
            } catch (\Exception $e) {
                // Index may not exist if it was never created (e.g., MySQL FK already had it).
            }
        }
    }
};
