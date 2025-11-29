<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivities extends BaseWidget
{
    protected static ?string $heading = 'Aktivitas Terkini';

    // Urutan widget di dashboard (boleh diubah)
    protected static ?int $sort = 2;

    /**
     * QUERY sumber data untuk tabel.
     * WAJIB ada untuk TableWidget (ini yang dicari Filament).
     */
    protected function getTableQuery(): Builder
    {
        return Transaction::query()
            ->latest('created_at')
            ->limit(10); // ambil 10 transaksi terakhir
    }

    /**
     * Definisi tampilan tabel.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Transaksi')
                    ->sortable(),

                // Ganti nama kolom ini sesuai field yang pasti ada
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since(), // tampil “10 menit lalu”
            ])
            ->paginated(false); // tanpa pagination
    }
}
