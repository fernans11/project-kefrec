<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('type'); // in / out
            $table->string('category')->nullable();
            $table->integer('amount')->default(0);
            $table->string('source')->nullable(); // manual / sales_return / purchase_return
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashflows');
    }
};
