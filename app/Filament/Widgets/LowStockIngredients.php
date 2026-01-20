<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockIngredients extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Stok Menipis';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Bahan Baku')
                    ->searchable(),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->formatStateUsing(fn ($state, $record) => rtrim(rtrim(number_format((float) $state, 2, ',', '.'), '0'), ',') . ' ' . $record->unit),
                TextColumn::make('min_stock')
                    ->label('Min Stok')
                    ->formatStateUsing(fn ($state, $record) => rtrim(rtrim(number_format((float) $state, 2, ',', '.'), '0'), ',') . ' ' . $record->unit),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }

    protected function getTableQuery(): Builder
    {
        return Ingredient::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock');
    }
}
