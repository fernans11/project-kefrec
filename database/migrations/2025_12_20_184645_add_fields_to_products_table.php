<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Basic info
            $table->string('category')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();

            $table->string('name')->index();
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();

            // Pricing
            $table->integer('price')->default(0);

            // Media
            $table->string('image_url')->nullable();

            // Flags
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_popular')->default(false)->index();
            $table->boolean('is_new')->default(false)->index();

            // Rating / ordering
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('sort_order')->default(0)->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'category_id',
                'name',
                'slug',
                'description',
                'price',
                'image_url',
                'is_active',
                'is_popular',
                'is_new',
                'rating',
                'sort_order',
            ]);
        });
    }
};
