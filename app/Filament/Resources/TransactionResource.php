<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
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
                            'draft' => 'Draft',
                            'paid' => 'Paid',
                            'processing' => 'Processing',
                            'ready' => 'Ready',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('draft'),

                    Forms\Components\Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Cash',
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
                        ->label('Customer (Opsional)')
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
                    ->label('Customer')
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
                        'cash' => 'Cash',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                        default => '-',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'paid' => 'Paid',
                        'processing' => 'Processing',
                        'ready' => 'Ready',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
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
                    'draft' => 'Draft',
                    'paid' => 'Paid',
                    'processing' => 'Processing',
                    'ready' => 'Ready',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('payment_method')->options([
                    'cash' => 'Cash',
                    'qris' => 'QRIS',
                    'transfer' => 'Transfer',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // memastikan relasi terbaca cepat di tabel
        return parent::getEloquentQuery()->with(['cashier', 'customer']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
