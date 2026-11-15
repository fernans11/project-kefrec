<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Pembelian Bahan Baku';
    protected static ?string $modelLabel = 'Pembelian';
    protected static ?string $pluralModelLabel = 'Pembelian';
    protected static ?string $navigationGroup = 'Pembelian';
    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pembelian')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('invoice_no')
                        ->label('Nomor PO')
                        ->disabled()
                        ->dehydrated()
                        ->default(fn () => Purchase::generateInvoiceNo())
                        ->required(),
                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(fn () => Supplier::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Pilih pemasok tempat bahan baku dibeli. Data ini menjadi asal pembelian.'),
                    Forms\Components\DatePicker::make('purchased_at')
                        ->label('Tanggal Pembelian')
                        ->default(now()),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draf - belum menambah stok',
                            'received' => 'Diterima - stok dan cashflow diproses',
                            'cancelled' => 'Dibatalkan - tidak diproses',
                        ])
                        ->default('draft')
                        ->required()
                        ->helperText('Gunakan Diterima hanya jika bahan baku sudah benar-benar diterima oleh cafe.')
                        ->disableOptionWhen(function (string $value, ?Purchase $record): bool {
                            if (! $record) {
                                return false;
                            }

                            return ! Purchase::canTransition((string) $record->status, $value);
                        }),
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->columnSpanFull()
                        ->rows(2),
                ]),

            Forms\Components\Section::make('Item Pembelian')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label('Daftar Item')
                        ->relationship()
                        ->defaultItems(1)
                        ->columns(12)
                        ->schema([
                            Forms\Components\Select::make('ingredient_id')
                                ->label('Bahan Baku')
                                ->columnSpan(6)
                                ->options(fn () => Ingredient::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray()
                                )
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('qty')
                                ->label('Qty')
                                ->columnSpan(2)
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $qty = (float) ($state ?? 0);
                                    $price = (int) ($get('price') ?? 0);
                                    $set('subtotal', (int) round($qty * $price, 0));
                                }),
                            Forms\Components\TextInput::make('price')
                                ->label('Harga')
                                ->columnSpan(2)
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->helperText('Isi harga per unit tanpa titik atau koma.')
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $qty = (float) ($get('qty') ?? 0);
                                    $price = (int) ($state ?? 0);
                                    $set('subtotal', (int) round($qty * $price, 0));
                                }),
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->columnSpan(2)
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->default(0),
                        ])
                        ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::normalizeItemData($data))
                        ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::normalizeItemData($data))
                        ->afterStateUpdated(function (callable $get, callable $set) {
                            self::recalcTotals($get, $set);
                        })
                        ->reactive(),
                ]),

            Forms\Components\Section::make('Total')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->default(0),
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
                ]),
        ]);
    }

    protected static function recalcTotals(callable $get, callable $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = self::calculateItemsSubtotal($items);

        $tax = (int) ($get('tax') ?? 0);
        $total = max(0, $subtotal + $tax);

        $set('subtotal', $subtotal);
        $set('total', $total);
    }

    public static function normalizeItemData(array $data): array
    {
        $qty = (float) ($data['qty'] ?? 0);
        $price = (int) ($data['price'] ?? 0);

        $data['qty'] = max(0, $qty);
        $data['price'] = max(0, $price);
        $data['subtotal'] = (int) round($data['qty'] * $data['price'], 0);

        return $data;
    }

    public static function calculateItemsSubtotal(array $items): int
    {
        $subtotal = 0;

        foreach ($items as $row) {
            $normalized = self::normalizeItemData((array) $row);
            $subtotal += $normalized['subtotal'];
        }

        return $subtotal;
    }

    public static function normalizePurchaseData(array $data): array
    {
        $data['subtotal'] = self::calculateItemsSubtotal($data['items'] ?? []);
        $data['tax'] = max(0, (int) ($data['tax'] ?? 0));
        $data['total'] = max(0, $data['subtotal'] + $data['tax']);

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draf',
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchased_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draf',
                    'received' => 'Diterima',
                    'cancelled' => 'Dibatalkan',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (Purchase $record): bool => $record->status !== 'received' && blank($record->received_at)),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
