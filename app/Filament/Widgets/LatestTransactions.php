<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactions extends BaseWidget
{
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->default('-')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending_payment' => 'Menunggu Pembayaran',
                        'pending_cashier' => 'Menunggu Kasir',
                        'paid' => 'Dibayar',
                        'processing' => 'Diproses',
                        'ready' => 'Siap Diambil',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->heading('Transaksi Terbaru');
    }
}
