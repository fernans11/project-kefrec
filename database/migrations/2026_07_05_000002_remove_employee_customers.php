<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('customers')
            ->whereIn('user_id', function ($query) {
                $query->select('id')
                    ->from('users')
                    ->whereIn('usertype', ['cashier', 'kasir', 'kitchen', 'dapur']);
            })
            ->delete();
    }

    public function down(): void
    {
        //
    }
};
