<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PeriodicReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Periodik';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 210;
    protected static ?string $title = 'Laporan Periodik';

    protected static string $view = 'filament.pages.periodic-report';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getReportQuery())
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        return Carbon::parse($state)->format('d M Y');
                    }),
                Tables\Columns\TextColumn::make('jumlah_order')
                    ->label('Jumlah Order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('diskon')
                    ->label('Diskon')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('pajak')
                    ->label('Pajak')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Selesai',
                        'ready' => 'Siap Diambil',
                        'processing' => 'Diproses',
                        'paid' => 'Dibayar',
                        'pending_cashier' => 'Menunggu Kasir',
                        'draft' => 'Draf',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('completed'),
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('tanggal', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('export_csv')
                    ->label('Export CSV (Ringkasan)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $records = $this->getFilteredTableQuery()->get();
                        $timestamp = now()->format('Ymd_His');
                        $filename = "laporan_periodik_{$timestamp}.csv";

                        return response()->streamDownload(function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, [
                                'Tanggal',
                                'Jumlah Order',
                                'Subtotal',
                                'Diskon',
                                'Pajak',
                                'Total',
                            ]);

                            foreach ($records as $row) {
                                fputcsv($handle, [
                                    $row->tanggal,
                                    $row->jumlah_order,
                                    $row->subtotal,
                                    $row->diskon,
                                    $row->pajak,
                                    $row->total,
                                ]);
                            }

                            fclose($handle);
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
            ]);
    }

    private function getReportQuery(): Builder
    {
        return Transaction::query()
            ->selectRaw('MIN(id) as id')
            ->selectRaw('DATE(created_at) as tanggal')
            ->selectRaw('COUNT(*) as jumlah_order')
            ->selectRaw('SUM(subtotal) as subtotal')
            ->selectRaw('SUM(discount) as diskon')
            ->selectRaw('SUM(tax) as pajak')
            ->selectRaw('SUM(total) as total')
            ->groupBy('tanggal');
    }
}
