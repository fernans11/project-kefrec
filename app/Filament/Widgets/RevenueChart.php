<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChart extends LineChartWidget
{
    protected static ?int $sort = 3;

    public ?string $filter = '7';

    public function getHeading(): string
    {
        return 'Grafik Pemasukan';
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
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $data,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
