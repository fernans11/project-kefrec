<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Komplain';
    protected static ?string $modelLabel = 'Komplain';
    protected static ?string $pluralModelLabel = 'Komplain';
    protected static ?string $navigationGroup = 'Layanan';
    protected static ?int $navigationSort = 110;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pelanggan')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->label('Pelanggan')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\TextInput::make('name')
                        ->label('Nama')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label('Telepon')
                        ->maxLength(50),
                ]),
            Forms\Components\Section::make('Detail Komplain')
                ->columns(2)
                ->schema([
                    Forms\Components\Textarea::make('message')
                        ->label('Pesan Komplain')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'open' => 'Terbuka',
                            'progress' => 'Diproses',
                            'closed' => 'Selesai',
                        ])
                        ->default('open')
                        ->required()
                        ->disableOptionWhen(function (string $value, ?Complaint $record): bool {
                            if (! $record) {
                                return false;
                            }

                            return ! Complaint::canTransition((string) $record->status, $value);
                        }),
                    Forms\Components\Textarea::make('response')
                        ->label('Tindak Lanjut')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open' => 'Terbuka',
                        'progress' => 'Diproses',
                        'closed' => 'Selesai',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'open' => 'Terbuka',
                    'progress' => 'Diproses',
                    'closed' => 'Selesai',
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
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
