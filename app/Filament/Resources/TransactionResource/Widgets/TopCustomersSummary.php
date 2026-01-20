<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class TopCustomersSummary extends BaseWidget
{
    protected static ?string $heading = 'Pelanggan Teratas';

    protected static ?int $sort = 5;

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
                    ->selectRaw('customers.name as customer, COUNT(*) as orders, SUM(transactions.total) as total')
                    ->join('customers', 'customers.id', '=', 'transactions.customer_id')
                    ->whereBetween('transactions.created_at', [$start->startOfDay(), $end->endOfDay()])
                    ->where('transactions.status', 'completed')
                    ->groupBy('customers.name')
                    ->orderBy('total', 'desc')
            )
            ->columns([
                TextColumn::make('customer')
                    ->label('Pelanggan')
                    ->default('-'),
                TextColumn::make('orders')
                    ->label('Jumlah Order'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true),
            ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return (string) ($record->customer ?? 'lainnya');
    }
}
