<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\PieChartWidget;

class PaymentMethodPie extends PieChartWidget
{
    protected static ?int $sort = 4;

    public ?string $filter = '7';

    public function getHeading(): string
    {
        return 'Pemasukan per Metode';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari',
            '30' => '30 Hari',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?: 7);
        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::today();

        $rows = Transaction::query()
            ->selectRaw('payment_method, SUM(total) as total')
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        $labels = [];
        $data = [];

        $labelMap = [
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
        ];

        foreach ($rows as $method => $total) {
            $labels[] = $labelMap[$method] ?? ($method ?: 'Lainnya');
            $data[] = (int) $total;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => [
                        '#f59e0b',
                        '#22c55e',
                        '#3b82f6',
                        '#ef4444',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }
}
