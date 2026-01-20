<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class CategorySummary extends BaseWidget
{
    protected static ?string $heading = 'Ringkasan Kategori Menu';

    protected static ?int $sort = 3;

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
                TransactionItem::query()
                    ->selectRaw('products.category as category, SUM(transaction_items.qty) as qty, SUM(transaction_items.subtotal) as total')
                    ->join('products', 'products.id', '=', 'transaction_items.product_id')
                    ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
                    ->whereBetween('transactions.created_at', [$start->startOfDay(), $end->endOfDay()])
                    ->where('transactions.status', 'completed')
                    ->groupBy('products.category')
                    ->orderBy('total', 'desc')
            )
            ->columns([
                TextColumn::make('category')
                    ->label('Kategori')
                    ->default('-'),
                TextColumn::make('qty')
                    ->label('Jumlah Terjual'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true),
            ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return (string) ($record->category ?? 'lainnya');
    }
}
