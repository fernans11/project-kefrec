<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Filament\Resources\ProductResource\RelationManagers\IngredientsRelationManager;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produk / Menu';
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Menu')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Menu')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Otomatis dari Nama Menu. Bisa diedit bila perlu.'),

                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->required()
                            ->options([
                                'Minuman' => 'Minuman',
                                'Makanan' => 'Makanan',
                                'Platters' => 'Platters',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('price')
                            ->label('Harga (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->rows(3),

                        Forms\Components\TextInput::make('image_url')
                            ->label('Image URL')
                            ->placeholder('https://...')
                            ->columnSpanFull()
                            ->helperText('Saat ini pakai URL gambar. Nanti bisa kita upgrade ke upload image.'),
                    ]),

                Forms\Components\Section::make('Stok')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Toggle::make('track_stock')
                            ->label('Aktifkan Tracking Stok')
                            ->helperText('Jika ON, stok akan dikelola dan bisa dikurangi saat transaksi.')
                            ->default(true),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Isi stok awal. Jika tracking stok OFF, stok boleh 0.')
                            ->disabled(fn ($get) => ! (bool) $get('track_stock')),

                        Forms\Components\Placeholder::make('stock_hint')
                            ->label('Catatan')
                            ->content('Jika menu tidak punya stok (misal minuman selalu tersedia), kamu bisa set tracking stok OFF.')
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Pengaturan & Status')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),

                        Forms\Components\Toggle::make('is_popular')
                            ->label('Best Seller Otomatis')
                            ->helperText('Dihitung otomatis dari 2 menu terlaris per kategori berdasarkan transaksi selesai.')
                            ->default(false)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Toggle::make('is_new')
                            ->label('Produk Baru Otomatis')
                            ->helperText('Aktif otomatis saat menu baru dibuat.')
                            ->default(false)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('rating')
                            ->label('Rating')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1)
                            ->default(0),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->withSum(['completedTransactionItems as completed_order_qty' => fn ($query) => $query], 'qty')
                ->withSum(['completedTransactionItems as completed_order_revenue' => fn ($query) => $query], 'subtotal'))
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Gambar')
                    ->square()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', true)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('track_stock')
                    ->label('Track Stok')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state, $record) => (bool) $record->track_stock ? (string) $state : '-'),

                Tables\Columns\TextColumn::make('completed_order_qty')
                    ->label('Terjual')
                    ->numeric()
                    ->default(0)
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_order_revenue')
                    ->label('Total Penjualan')
                    ->money('IDR', true)
                    ->default(0)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_popular')
                    ->label('Best Seller')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_new')
                    ->label('Baru')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'Minuman' => 'Minuman',
                        'Makanan' => 'Makanan',
                        'Platters' => 'Platters',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
                Tables\Filters\TernaryFilter::make('is_popular')->label('Best Seller'),
                Tables\Filters\TernaryFilter::make('is_new')->label('Baru'),
                Tables\Filters\TernaryFilter::make('track_stock')->label('Track Stok'),
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
            IngredientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
