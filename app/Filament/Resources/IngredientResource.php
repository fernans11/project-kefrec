<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Filament\Resources\IngredientResource\RelationManagers;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Bahan Baku';
    protected static ?string $modelLabel = 'Bahan Baku';
    protected static ?string $pluralModelLabel = 'Bahan Baku';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 20;

    public static function getNavigationBadge(): ?string
    {
        $count = Ingredient::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit')
                    ->required()
                    ->maxLength(255)
                    ->default('pcs'),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('min_stock')
                    ->required()
                    ->numeric()
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => rtrim(rtrim(number_format((float) $state, 2, ',', '.'), '0'), ',') . ' ' . $record->unit),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Min Stok')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => rtrim(rtrim(number_format((float) $state, 2, ',', '.'), '0'), ',') . ' ' . $record->unit),
                Tables\Columns\IconColumn::make('low_stock')
                    ->label('Menipis')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (float) $record->stock <= (float) $record->min_stock),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn ($query) => $query->whereColumn('stock', '<=', 'min_stock')),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }
}
