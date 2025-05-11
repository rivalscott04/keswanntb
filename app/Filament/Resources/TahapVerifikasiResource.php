<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TahapVerifikasiResource\Pages;
use App\Filament\Resources\TahapVerifikasiResource\RelationManagers;
use App\Models\TahapVerifikasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasNavigationVisibility;
use App\Filament\Traits\HasAdminOnlyAccess;

class TahapVerifikasiResource extends Resource
{
    use HasNavigationVisibility;
    use HasAdminOnlyAccess;

    protected static ?string $model = TahapVerifikasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Tahap Verifikasi';
    protected static ?string $slug = 'tahap-verifikasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('urutan')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('urutan')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTahapVerifikasi::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->wewenang->nama === 'Administrator';
    }
}
