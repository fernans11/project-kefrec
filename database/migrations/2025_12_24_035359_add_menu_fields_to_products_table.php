<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            if (!Schema::hasColumn('products', 'name')) {
                $table->string('name');
            }

            if (!Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->nullable()->unique();
            }

            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable();
            }

            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable();
            }

            if (!Schema::hasColumn('products', 'price')) {
                $table->unsignedInteger('price')->default(0);
            }

            if (!Schema::hasColumn('products', 'image_url')) {
                $table->string('image_url')->nullable();
            }

            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (!Schema::hasColumn('products', 'is_popular')) {
                $table->boolean('is_popular')->default(false);
            }

            if (!Schema::hasColumn('products', 'is_new')) {
                $table->boolean('is_new')->default(false);
            }

            if (!Schema::hasColumn('products', 'rating')) {
                $table->decimal('rating', 2, 1)->nullable();
            }

            if (!Schema::hasColumn('products', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $columns = [
                'slug',
                'category',
                'description',
                'price',
                'image_url',
                'is_active',
                'is_popular',
                'is_new',
                'rating',
                'sort_order',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
