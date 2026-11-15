<?php

namespace Tests\Feature;

use App\Filament\Widgets\OverviewStats;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverviewStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_pemasukan_only_counts_completed_transactions(): void
    {
        Transaction::create([
            'invoice_no' => 'INV-001',
            'subtotal' => 100000,
            'discount' => 0,
            'tax' => 0,
            'total' => 100000,
            'status' => 'completed',
        ]);

        Transaction::create([
            'invoice_no' => 'INV-002',
            'subtotal' => 999999,
            'discount' => 0,
            'tax' => 0,
            'total' => 999999,
            'status' => 'pending_cashier',
        ]);

        $widget = new OverviewStats();

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getStats');
        $method->setAccessible(true);

        $stats = $method->invoke($widget);

        $this->assertCount(5, $stats);
        $this->assertSame('Total Pemasukan', $stats[1]->getLabel());
        $this->assertSame('Rp 100.000', $stats[1]->getValue());
    }
}
