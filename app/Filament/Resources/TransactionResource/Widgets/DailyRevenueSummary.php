<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DailyRevenueSummary extends BaseWidget
{
    protected static ?string $heading = 'Ringkasan Harian';

    protected static ?int $sort = 1;

    public ?string $filter = '7';

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari',
            '30' => '30 Hari',
        ];
    }

    public function table(Table $table): Table
    {
        $days = (int) ($this->filter ?: 7);
        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::today();

        return $table
            ->query(
                Transaction::query()
                    ->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(subtotal) as subtotal, SUM(discount) as discount, SUM(tax) as tax, SUM(total) as total')
                    ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                    ->where('status', 'completed')
                    ->groupBy('day')
                    ->orderBy('day', 'desc')
            )
            ->columns([
                TextColumn::make('day')
                    ->label('Tanggal')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d M Y')),
                TextColumn::make('orders')
                    ->label('Jumlah Order'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR', true),
                TextColumn::make('discount')
                    ->label('Diskon')
                    ->money('IDR', true),
                TextColumn::make('tax')
                    ->label('Pajak')
                    ->money('IDR', true),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true),
            ]);
    }

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) $record->day;
    }
}
