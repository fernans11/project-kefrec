<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseReturnResource\Pages;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseReturnResource extends Resource
{
    protected static ?string $model = PurchaseReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-right';
    protected static ?string $navigationLabel = 'Retur Pembelian';
    protected static ?string $modelLabel = 'Retur Pembelian';
    protected static ?string $pluralModelLabel = 'Retur Pembelian';
    protected static ?string $navigationGroup = 'Retur';
    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Retur')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('return_no')
                        ->label('Nomor Retur')
                        ->disabled()
                        ->dehydrated()
                        ->default(fn () => PurchaseReturn::generateReturnNo())
                        ->required(),
                    Forms\Components\Select::make('purchase_id')
                        ->label('Pembelian')
                        ->options(fn () => Purchase::query()
                            ->orderByDesc('id')
                            ->pluck('invoice_no', 'id')
                            ->toArray()
                        )
                        ->searchable()
                        ->nullable(),
                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(fn () => Supplier::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                        )
                        ->searchable()
                        ->nullable(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draf',
                            'processed' => 'Diproses',
                            'cancelled' => 'Dibatalkan',
                        ])
                        ->default('draft')
                        ->required()
                        ->disableOptionWhen(function (string $value, ?PurchaseReturn $record): bool {
                            if (! $record) {
                                return false;
                            }

                            return ! PurchaseReturn::canTransition((string) $record->status, $value);
                        }),
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->columnSpanFull()
                        ->rows(2),
                ]),

            Forms\Components\Section::make('Item Retur')
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
                                ->reactive()
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
                                ->reactive()
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
        $subtotal = 0;

        foreach ($items as $row) {
            $subtotal += (int) ($row['subtotal'] ?? 0);
        }

        $tax = (int) ($get('tax') ?? 0);
        $total = max(0, $subtotal + $tax);

        $set('subtotal', $subtotal);
        $set('total', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('return_no')
                    ->label('Nomor Retur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase.invoice_no')
                    ->label('Pembelian')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draf',
                        'processed' => 'Diproses',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draf',
                    'processed' => 'Diproses',
                    'cancelled' => 'Dibatalkan',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('proses')
                    ->label('Proses')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        $record->update(['status' => 'processed']);
                        $record->applyReturnIfNeeded();
                        \App\Models\Cashflow::create([
                            'date' => now(),
                            'type' => 'in',
                            'category' => 'Retur Pembelian',
                            'amount' => $record->total,
                            'source' => 'purchase_return',
                            'notes' => 'Retur pembelian ' . $record->return_no,
                        ]);
                    }),
                Tables\Actions\EditAction::make()->label('Ubah'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Hapus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseReturns::route('/'),
            'create' => Pages\CreatePurchaseReturn::route('/create'),
            'edit' => Pages\EditPurchaseReturn::route('/{record}/edit'),
        ];
    }
}
