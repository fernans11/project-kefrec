<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashflowResource\Pages;
use App\Models\Cashflow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CashflowResource extends Resource
{
    protected static ?string $model = Cashflow::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Keuangan';
    protected static ?string $modelLabel = 'Keuangan';
    protected static ?string $pluralModelLabel = 'Keuangan';
    protected static ?string $navigationGroup = 'Keuangan';
    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')
                ->label('Tanggal')
                ->default(now())
                ->required(),
            Forms\Components\Select::make('type')
                ->label('Tipe')
                ->options([
                    'in' => 'Pemasukan',
                    'out' => 'Pengeluaran',
                ])
                ->required(),
            Forms\Components\TextInput::make('category')
                ->label('Kategori')
                ->maxLength(255),
            Forms\Components\TextInput::make('amount')
                ->label('Nominal')
                ->numeric()
                ->minValue(0)
                ->required(),
            Forms\Components\Select::make('source')
                ->label('Sumber')
                ->options([
                    'manual' => 'Manual',
                    'sales_return' => 'Retur Penjualan',
                    'purchase_return' => 'Retur Pembelian',
                    'purchase' => 'Pembelian',
                    'sales' => 'Penjualan',
                ])
                ->default('manual')
                ->required(),
            Forms\Components\Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'in' ? 'Pemasukan' : 'Pengeluaran'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'manual' => 'Manual',
                        'sales_return' => 'Retur Penjualan',
                        'purchase_return' => 'Retur Pembelian',
                        'purchase' => 'Pembelian',
                        'sales' => 'Penjualan',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    'in' => 'Pemasukan',
                    'out' => 'Pengeluaran',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('Hapus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashflows::route('/'),
            'create' => Pages\CreateCashflow::route('/create'),
            'edit' => Pages\EditCashflow::route('/{record}/edit'),
        ];
    }
}
