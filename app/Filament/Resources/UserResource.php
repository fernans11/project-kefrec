<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\UserResource\Pages\CreateUser;

class UserResource extends Resource
{

    protected static ?string $navigationGroup = 'Akun Internal';
    protected static ?int $navigationSort = 100;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Admin / Owner / Karyawan';
    protected static ?string $modelLabel = 'Akun Internal';
    protected static ?string $pluralModelLabel = 'Akun Internal';
    protected static bool $shouldRegisterNavigation = false;

    public static function getStaffUserTypes(): array
    {
        return ['admin', 'owner', 'kasir', 'dapur'];
    }

    public static function getRoleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Admin',
            'owner' => 'Owner',
            'kasir' => 'Karyawan',
            'dapur' => 'Karyawan',
            'user' => 'Pelanggan',
            default => ucfirst((string) $role),
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('usertype')
                    ->label('Role')
                    ->required()
                    ->options([
                        'admin' => self::getRoleLabel('admin'),
                        'owner' => self::getRoleLabel('owner'),
                        'kasir' => self::getRoleLabel('kasir'),
                        'dapur' => self::getRoleLabel('dapur'),
                    ])
                    ->default('kasir'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($livewire) => $livewire instanceof CreateUser),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->whereIn('usertype', self::getStaffUserTypes()))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usertype')
                    ->label('Role')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => self::getRoleLabel($state ?? '')),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('usertype')
                    ->label('Kategori Role')
                    ->options([
                        'admin' => self::getRoleLabel('admin'),
                        'owner' => self::getRoleLabel('owner'),
                        'kasir' => self::getRoleLabel('kasir'),
                        'dapur' => self::getRoleLabel('dapur'),
                    ])
                    ->query(fn ($query, $state) => $query->where('usertype', $state)),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    
    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
