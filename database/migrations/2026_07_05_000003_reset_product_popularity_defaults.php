<?php

use App\Support\BestSellerUpdater;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->update([
            'is_popular' => false,
            'is_new' => false,
        ]);

        BestSellerUpdater::sync();
    }

    public function down(): void
    {
        DB::table('products')->update([
            'is_popular' => false,
            'is_new' => false,
        ]);
    }
};
