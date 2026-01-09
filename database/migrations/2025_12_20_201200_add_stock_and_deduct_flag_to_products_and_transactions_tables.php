<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('description');
            $table->boolean('track_stock')->default(true)->after('stock'); // kalau ada produk unlimited, set false
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('stock_deducted_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock', 'track_stock']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('stock_deducted_at');
        });
    }
};
