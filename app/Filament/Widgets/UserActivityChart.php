<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class UserActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Aktivitas User Hari Ini';

    protected function getData(): array
    {
        $today = now()->toDateString();

        // kelompokkan transaksi per jam di hari ini
        $data = Transaction::selectRaw('HOUR(paid_at) as hour, COUNT(*) as total')
            ->whereDate('paid_at', $today)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = $data->pluck('hour')->map(fn ($h) => sprintf('%02d:00', $h));
        $values = $data->pluck('total');

        return [
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => $values,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        // tipe chart kita line
        return 'line';
    }

    protected int | string | array $columnSpan = 'full';
}
