<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\Widgets\DailyRevenueSummary;
use App\Filament\Resources\TransactionResource\Widgets\PaymentMethodSummary;
use App\Filament\Resources\TransactionResource\Widgets\CategorySummary;
use App\Filament\Resources\TransactionResource\Widgets\TopProductsSummary;
use App\Filament\Resources\TransactionResource\Widgets\TopCustomersSummary;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Transaksi';
    protected static ?string $modelLabel = 'Transaksi';
    protected static ?string $pluralModelLabel = 'Transaksi';
    protected static ?string $navigationGroup = 'Manajemen Transaksi';
    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Transaksi')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('invoice_no')
                        ->label('Invoice')
                        ->disabled()
                        ->dehydrated()
                        ->default(fn () => Transaction::generateInvoiceNo())
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->required()
                        ->options([
                            'draft' => 'Draf',
                            'pending_cashier' => 'Menunggu Kasir',
                            'paid' => 'Dibayar',
                            'processing' => 'Diproses',
                            'ready' => 'Siap Diambil',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                        ])
                        ->default('draft')
                        ->disableOptionWhen(function (string $value, ?Transaction $record): bool {
                            if (! $record) {
                                return false;
                            }

                            return ! Transaction::canTransition((string) $record->status, $value);
                        }),

                    Forms\Components\Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai',
                            'qris' => 'QRIS',
                            'transfer' => 'Transfer',
                        ])
                        ->searchable()
                        ->nullable(),

                    Forms\Components\TextInput::make('cashier_id')
                        ->label('Cashier ID')
                        ->disabled()
                        ->dehydrated()
                        ->default(fn () => auth()->id())
                        ->required(),

                    Forms\Components\Select::make('customer_id')
                        ->label('Pelanggan (Opsional)')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ]),

            Forms\Components\Section::make('Item Transaksi')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label('Daftar Item')
                        ->relationship()
                        ->defaultItems(1)
                        ->reorderable()
                        ->columns(12)
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Produk')
                                ->columnSpan(5)
                                ->options(fn () => Product::query()
                                    ->where('is_active', true)
                                    ->orderBy('category')
                                    ->orderBy('sort_order')
                                    ->pluck('name', 'id')
                                    ->toArray()
                                )
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (!$state) return;
                                    $p = Product::find($state);
                                    if (!$p) return;
                                    $set('price', (int) $p->price);
                                }),

                            Forms\Components\TextInput::make('qty')
                                ->label('Qty')
                                ->columnSpan(2)
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $qty = (int) ($state ?? 0);
                                    $price = (int) ($get('price') ?? 0);
                                    $set('subtotal', max(0, $qty * $price));
                                }),

                            Forms\Components\TextInput::make('price')
                                ->label('Harga')
                                ->columnSpan(3)
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $qty = (int) ($get('qty') ?? 0);
                                    $price = (int) ($state ?? 0);
                                    $set('subtotal', max(0, $qty * $price));
                                }),

                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->columnSpan(2)
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->default(0),
                        ])
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            self::recalcTotals($get, $set);
                        })
                        ->reactive(),
                ]),

            Forms\Components\Section::make('Pembayaran & Total')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->default(0),

                    Forms\Components\TextInput::make('discount')
                        ->label('Diskon')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            self::recalcTotals($get, $set);
                        }),

                    Forms\Components\TextInput::make('tax')
                        ->label('Pajak')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            self::recalcTotals($get, $set);
                        }),

                    Forms\Components\TextInput::make('total')
                        ->label('Total')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->default(0),

                    Forms\Components\TextInput::make('paid_amount')
                        ->label('Bayar')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            $paid = (int) ($get('paid_amount') ?? 0);
                            $total = (int) ($get('total') ?? 0);
                            $set('change_amount', max(0, $paid - $total));
                        }),

                    Forms\Components\TextInput::make('change_amount')
                        ->label('Kembalian')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->default(0),
                ]),
        ]);
    }

    protected static function recalcTotals(callable $get, callable $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $row) {
            $subtotal += (int) ($row['subtotal'] ?? 0);
        }

        $discount = (int) ($get('discount') ?? 0);
        $tax = (int) ($get('tax') ?? 0);

        $total = max(0, $subtotal - $discount + $tax);

        $set('subtotal', $subtotal);
        $set('total', $total);

        $paid = (int) ($get('paid_amount') ?? 0);
        $set('change_amount', max(0, $paid - $total));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                        default => '-',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('items.product.category')
                    ->label('Kategori')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($record) {
                        $categories = $record->items
                            ->pluck('product.category')
                            ->filter()
                            ->unique()
                            ->values();
                        return $categories->isEmpty() ? '-' : $categories->join(', ');
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draf',
                        'pending_cashier' => 'Menunggu Kasir',
                        'paid' => 'Dibayar',
                        'processing' => 'Diproses',
                        'ready' => 'Siap Diambil',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draf',
                    'pending_cashier' => 'Menunggu Kasir',
                    'paid' => 'Dibayar',
                    'processing' => 'Diproses',
                    'ready' => 'Siap Diambil',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                ]),
                Tables\Filters\SelectFilter::make('payment_method')->options([
                    'cash' => 'Tunai',
                    'qris' => 'QRIS',
                    'transfer' => 'Transfer',
                ]),
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\Action::make('approve_cashier')
                    ->label('Setujui')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending_cashier')
                    ->action(function ($record) {
                        if (! Transaction::canTransition((string) $record->status, 'processing')) {
                            Notification::make()
                                ->title('Status tidak valid')
                                ->body('Transaksi tidak bisa disetujui dari status saat ini.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $record->update(['status' => 'processing']);
                    }),
                Tables\Actions\Action::make('mark_ready')
                    ->label('Siap')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'processing')
                    ->action(function ($record) {
                        if (! Transaction::canTransition((string) $record->status, 'ready')) {
                            Notification::make()
                                ->title('Status tidak valid')
                                ->body('Transaksi tidak bisa diubah ke status siap.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $record->update(['status' => 'ready']);
                    }),
                Tables\Actions\Action::make('mark_completed')
                    ->label('Selesai')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'ready')
                    ->action(function ($record) {
                        if (! Transaction::canTransition((string) $record->status, 'completed')) {
                            Notification::make()
                                ->title('Status tidak valid')
                                ->body('Transaksi tidak bisa diselesaikan dari status saat ini.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $record->update(['status' => 'completed']);
                        $record->deductStockIfNeeded();
                        $record->recordCashflowIfNeeded();
                    }),
                Tables\Actions\EditAction::make()->label('Ubah'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_csv')
                        ->label('Export CSV (Lengkap)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            $timestamp = now()->format('Ymd_His');
                            $filename = "transactions_{$timestamp}.zip";

                            $records->loadMissing(['customer', 'cashier', 'items.product']);

                            $tmpFile = tempnam(sys_get_temp_dir(), 'kefrec_csv_');
                            $zip = new \ZipArchive();
                            $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                            $summary = fopen('php://temp', 'w+');
                            fputcsv($summary, [
                                'Invoice',
                                'Tanggal',
                                'Customer',
                                'Kasir',
                                'Status',
                                'Metode Pembayaran',
                                'Subtotal',
                                'Diskon',
                                'Pajak',
                                'Total',
                                'Bayar',
                                'Kembalian',
                            ]);
                                foreach ($records as $record) {
                                    fputcsv($summary, [
                                        $record->invoice_no,
                                        $record->created_at?->format('Y-m-d H:i:s'),
                                        $record->customer?->name ?? '-',
                                        $record->cashier?->name ?? '-',
                                        match ($record->status) {
                                            'draft' => 'Draf',
                                            'pending_cashier' => 'Menunggu Kasir',
                                            'paid' => 'Dibayar',
                                            'processing' => 'Diproses',
                                            'ready' => 'Siap Diambil',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            default => $record->status,
                                        },
                                        match ($record->payment_method) {
                                            'cash' => 'Tunai',
                                            'qris' => 'QRIS',
                                            'transfer' => 'Transfer',
                                            default => $record->payment_method ?? '-',
                                        },
                                        $record->subtotal,
                                        $record->discount,
                                        $record->tax,
                                        $record->total,
                                        $record->paid_amount,
                                        $record->change_amount,
                                    ]);
                                }
                            rewind($summary);
                            $zip->addFromString('transactions_summary.csv', stream_get_contents($summary));
                            fclose($summary);

                            $items = fopen('php://temp', 'w+');
                            fputcsv($items, [
                                'Invoice',
                                'Tanggal',
                                'Customer',
                                'Status',
                                'Metode Pembayaran',
                                'Kategori',
                                'Produk',
                                'Qty',
                                'Harga',
                                'Subtotal Item',
                            ]);
                            foreach ($records as $record) {
                                foreach ($record->items as $item) {
                                    fputcsv($items, [
                                        $record->invoice_no,
                                        $record->created_at?->format('Y-m-d H:i:s'),
                                        $record->customer?->name ?? '-',
                                        match ($record->status) {
                                            'draft' => 'Draf',
                                            'pending_cashier' => 'Menunggu Kasir',
                                            'paid' => 'Dibayar',
                                            'processing' => 'Diproses',
                                            'ready' => 'Siap Diambil',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            default => $record->status,
                                        },
                                        match ($record->payment_method) {
                                            'cash' => 'Tunai',
                                            'qris' => 'QRIS',
                                            'transfer' => 'Transfer',
                                            default => $record->payment_method ?? '-',
                                        },
                                        $item->product?->category ?? '-',
                                        $item->product?->name ?? '-',
                                        $item->qty,
                                        $item->price,
                                        $item->subtotal,
                                    ]);
                                }
                            }
                            rewind($items);
                            $zip->addFromString('transactions_items.csv', stream_get_contents($items));
                            fclose($items);

                            $zip->close();

                            return response()->download($tmpFile, $filename, [
                                'Content-Type' => 'application/zip',
                            ])->deleteFileAfterSend(true);
                        }),
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // memastikan relasi terbaca cepat di tabel
        return parent::getEloquentQuery()->with(['cashier', 'customer', 'items.product']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            DailyRevenueSummary::class,
            PaymentMethodSummary::class,
            CategorySummary::class,
            TopProductsSummary::class,
            TopCustomersSummary::class,
        ];
    }
}
