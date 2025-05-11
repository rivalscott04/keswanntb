<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Sp3Resource\Pages;

class Sp3Resource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $label = 'Pengajuan SP3';

    protected static ?string $slug = 'sp3';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('wewenang', function ($query) {
                $query->where('nama', 'Pengguna');
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_verified_at')
                    ->label('Status Verifikasi')
                    ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('account_verified_by')
                    ->label('Diverifikasi Oleh')
                    ->formatStateUsing(fn($state) => $state ? User::find($state)?->name : '-'),
            ])
            ->filters([
                Filter::make('unverified')
                    ->label('Belum Terverifikasi')
                    ->query(fn(Builder $query): Builder => $query->whereNull('account_verified_at'))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSp3::route('/'),
            'view' => Pages\ViewSp3::route('/{record}'),
        ];
    }
}