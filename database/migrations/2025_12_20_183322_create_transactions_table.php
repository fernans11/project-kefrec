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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_no')->unique();

            // optional: jika transaksi dari member/customer
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            // kasir yang input transaksi (users)
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();

            $table->integer('subtotal')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('tax')->default(0);
            $table->integer('total')->default(0);

            $table->string('payment_method')->nullable(); // cash/qris/transfer
            $table->integer('paid_amount')->default(0);
            $table->integer('change_amount')->default(0);

            // alur kasir -> dapur
            $table->string('status')->default('draft');
            // draft, paid, processing, ready, completed, cancelled

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
