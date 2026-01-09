<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Item Transaksi';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->label('Produk')
                ->relationship('product', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $price = (int) (Product::find($state)?->price ?? 0);
                    $set('price', $price);
                    $set('qty', 1);
                    $set('subtotal', $price);
                }),

            Forms\Components\TextInput::make('qty')
                ->numeric()
                ->default(1)
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $price = (int) ($get('price') ?? 0);
                    $qty   = (int) ($state ?? 1);
                    $set('subtotal', $price * $qty);
                }),

            Forms\Components\TextInput::make('price')
                ->numeric()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $price = (int) ($state ?? 0);
                    $qty   = (int) ($get('qty') ?? 1);
                    $set('subtotal', $price * $qty);
                }),

            Forms\Components\TextInput::make('subtotal')
                ->numeric()
                ->disabled()
                ->dehydrated(true),
        ])->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Produk')->searchable(),
                Tables\Columns\TextColumn::make('qty')->numeric(),
                Tables\Columns\TextColumn::make('price')->numeric(),
                Tables\Columns\TextColumn::make('subtotal')->numeric(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->recalcTotals();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->recalcTotals();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->recalcTotals();
                    }),
            ]);
    }
}
