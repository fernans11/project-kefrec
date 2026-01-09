<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredients';

    protected static ?string $title = 'Resep (Bahan Baku)';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('qty')
                ->label('Kebutuhan per 1 Produk')
                ->numeric()
                ->minValue(0)
                ->required()
                ->helperText('Contoh: Kopi 10 gram untuk 1 cup Americano.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.qty')
                    ->label('Qty / Produk')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Bahan')
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Tambah Bahan ke Resep')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name'])
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()->label('Pilih Bahan'),

                        Forms\Components\TextInput::make('qty')
                            ->label('Kebutuhan per 1 Produk')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah Qty'),
                Tables\Actions\DetachAction::make()->label('Hapus dari Resep'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()->label('Hapus Banyak'),
            ]);
    }
}
