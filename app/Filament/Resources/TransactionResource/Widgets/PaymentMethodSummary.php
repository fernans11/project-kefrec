<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodSummary extends BaseWidget
{
    protected static ?string $heading = 'Ringkasan Metode Pembayaran';

    protected static ?int $sort = 2;

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
                    ->selectRaw('payment_method as method, COUNT(*) as orders, SUM(total) as total')
                    ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                    ->where('status', 'completed')
                    ->groupBy('method')
                    ->orderBy('total', 'desc')
            )
            ->columns([
                TextColumn::make('method')
                    ->label('Metode')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                        default => 'Lainnya',
                    }),
                TextColumn::make('orders')
                    ->label('Jumlah Order'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true),
            ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return (string) ($record->method ?? 'lainnya');
    }
}
