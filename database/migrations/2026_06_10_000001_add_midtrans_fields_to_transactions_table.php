<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->unique()->after('invoice_no');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            $table->string('snap_token')->nullable()->after('midtrans_transaction_id');
            $table->string('payment_status')->nullable()->after('payment_method');
            $table->string('payment_type')->nullable()->after('payment_status');
            $table->json('payment_payload')->nullable()->after('payment_type');
            $table->timestamp('paid_at')->nullable()->after('payment_payload');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']);
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_transaction_id',
                'snap_token',
                'payment_status',
                'payment_type',
                'payment_payload',
                'paid_at',
            ]);
        });
    }
};
