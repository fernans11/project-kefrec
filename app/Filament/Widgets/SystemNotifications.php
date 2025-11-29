<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class SystemNotifications extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Notifikasi Sistem';

    /**
     * Sumber data untuk tabel.
     * WAJIB ada di TableWidget (ini yang dicari Filament).
     */
    protected function getTableQuery(): Builder
    {
        return Notification::query()
            ->latest()     // dari yang terbaru
            ->limit(10);   // ambil 10 data saja (boleh diubah)
    }

    /**
     * Definisi tampilan tabel.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'warning' => 'warning',
                        'error'   => 'danger',
                        default   => 'success',
                    }),

                Tables\Columns\TextColumn::make('message')
                    ->label('Pesan')
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since(),           // tampil "10 menit lalu"
            ])
            ->paginated(false);         // tanpa pagination, seperti di desain Figma
    }
}
