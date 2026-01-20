<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalOrders = Transaction::count();
        $totalRevenue = Transaction::sum('total');
        $pendingCashier = Transaction::where('status', 'pending_cashier')->count();
        $processing = Transaction::where('status', 'processing')->count();
        $lowStock = Ingredient::whereColumn('stock', '<=', 'min_stock')->count();

        return [
            Stat::make('Total Pesanan', $totalOrders),
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalRevenue, 0, ',', '.')),
            Stat::make('Menunggu Kasir', $pendingCashier),
            Stat::make('Diproses Dapur', $processing),
            Stat::make('Stok Menipis', $lowStock),
        ];
    }
}
