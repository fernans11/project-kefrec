<?php

namespace Tests\Feature;

use Database\Seeders\DemoScenarioSeeder;
use Database\Seeders\IngredientSeeder;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoScenarioSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_scenario_is_complete_and_idempotent(): void
    {
        $this->seed([MenuSeeder::class, IngredientSeeder::class, DemoScenarioSeeder::class]);

        $this->assertSame(53, DB::table('customers')->count());
        $this->assertSame(112, DB::table('transactions')->where('invoice_no', 'like', 'INV-DEMO-%')->count());
        $this->assertSame(96, DB::table('transactions')->where('invoice_no', 'like', 'INV-DEMO-%')->where('status', 'completed')->count());
        $this->assertSame(5, DB::table('transactions')->where('invoice_no', 'like', 'INV-DEMO-LIVE-%')->where('status', 'pending_cashier')->count());
        $this->assertSame(5, DB::table('transactions')->where('invoice_no', 'like', 'INV-DEMO-LIVE-%')->where('status', 'processing')->count());
        $this->assertSame(12, DB::table('purchases')->where('invoice_no', 'like', 'PO-DEMO-%')->count());
        $this->assertSame(4, DB::table('sales_returns')->where('return_no', 'like', 'RET-DEMO-%')->count());
        $this->assertSame(3, DB::table('purchase_returns')->where('return_no', 'like', 'RET-PO-DEMO-%')->count());
        $this->assertSame(8, DB::table('complaints')->where('title', 'like', '[DEMO]%')->count());
        $this->assertSame(2, DB::table('staff')->count());
        $this->assertEqualsCanonicalizing(['Budi Dapur', 'Siti Kasir'], DB::table('staff')->pluck('name')->all());
        $this->assertGreaterThan(40, DB::table('attendances')->count());
        $this->assertSame(0, DB::table('transactions')->select('invoice_no')->groupBy('invoice_no')->havingRaw('COUNT(*) > 1')->count());
        $this->assertSame(0, DB::table('transactions')->whereNotExists(function ($query) {
            $query->selectRaw('1')->from('transaction_items')->whereColumn('transaction_items.transaction_id', 'transactions.id');
        })->count());
        $period = [now()->subDays(29)->toDateString(), now()->toDateString()];
        $income = (int) DB::table('cashflows')->whereBetween('date', $period)->where('type', 'in')->sum('amount');
        $expense = (int) DB::table('cashflows')->whereBetween('date', $period)->where('type', 'out')->sum('amount');
        $this->assertGreaterThan($expense, $income);

        $counts = $this->demoCounts();
        $this->seed(DemoScenarioSeeder::class);

        $this->assertSame($counts, $this->demoCounts());
    }

    private function demoCounts(): array
    {
        return [
            DB::table('users')->where('email', 'like', '%@kefrec.test')->count(),
            DB::table('transactions')->where('invoice_no', 'like', 'INV-DEMO-%')->count(),
            DB::table('transaction_items')->whereIn('transaction_id', DB::table('transactions')->select('id')->where('invoice_no', 'like', 'INV-DEMO-%'))->count(),
            DB::table('cashflows')->whereIn('source', ['sales', 'purchase', 'sales_return', 'purchase_return', 'manual_demo'])->count(),
        ];
    }
}
