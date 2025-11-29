<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonitoringStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->toDateString();

        $todayTransactions = Transaction::whereDate('paid_at', $today)->count();

        $todayRevenue = Transaction::whereDate('paid_at', $today)
            ->sum('total_amount');

        $activeUsers = Transaction::whereDate('paid_at', $today)
            ->distinct('user_id')
            ->count('user_id');

        // sementara: pesanan aktif = transaksi hari ini
        $activeOrders = $todayTransactions;

        return [
            Stat::make('Total Transaksi Hari Ini', $todayTransactions)
                ->description('+/- dibanding kemarin (dummy)')
                ->icon('heroicon-o-receipt-refund'),

            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description('+/- % dari target (dummy)')
                ->icon('heroicon-o-banknotes'),

            Stat::make('User Aktif Hari Ini', $activeUsers)
                ->description('Kasir / Admin yang bertransaksi')
                ->icon('heroicon-o-user-group'),

            Stat::make('Pesanan Aktif', $activeOrders)
                ->description('Jumlah pesanan yang diproses (dummy)')
                ->icon('heroicon-o-bolt'),
        ];
    }
}
