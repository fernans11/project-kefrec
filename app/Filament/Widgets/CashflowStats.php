<?php

namespace App\Filament\Widgets;

use App\Models\Cashflow;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashflowStats extends BaseWidget
{
    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari',
            '30' => '30 Hari',
        ];
    }

    protected function getStats(): array
    {
        $days = (int) ($this->filter ?: 30);
        $start = Carbon::today()->subDays($days - 1)->startOfDay();
        $end = Carbon::today()->endOfDay();

        $income = Cashflow::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('type', 'in')
            ->sum('amount');

        $expense = Cashflow::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('type', 'out')
            ->sum('amount');

        $balance = $income - $expense;

        return [
            Stat::make('Pemasukan Lain', 'Rp ' . number_format($income, 0, ',', '.')),
            Stat::make('Pengeluaran Lain', 'Rp ' . number_format($expense, 0, ',', '.')),
            Stat::make('Saldo Bersih', 'Rp ' . number_format($balance, 0, ',', '.')),
        ];
    }
}
