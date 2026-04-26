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
        // Cek apakah tabel sudah punya kolom atau belum
        if (!Schema::hasColumn('transactions', 'total_amount')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('buyer_id')->nullable()->after('id');
                $table->unsignedBigInteger('photographer_id')->nullable()->after('buyer_id');
                $table->decimal('total_amount', 12, 2)->default(0)->after('photographer_id');
                $table->enum('status', ['pending', 'paid', 'completed', 'cancelled'])->default('pending')->after('total_amount');
            });
        }

        if (!Schema::hasColumn('transaction_items', 'transaction_id')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->unsignedBigInteger('transaction_id')->nullable()->after('id');
                $table->unsignedBigInteger('photo_id')->nullable()->after('transaction_id');
                $table->decimal('price', 12, 2)->default(0)->after('photo_id');
                $table->integer('quantity')->default(1)->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['buyer_id', 'photographer_id', 'total_amount', 'status']);
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'photo_id', 'price', 'quantity']);
        });
    }
};
