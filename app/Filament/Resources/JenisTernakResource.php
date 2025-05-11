<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisTernakResource\Pages;
use App\Filament\Resources\JenisTernakResource\RelationManagers;
use App\Models\JenisTernak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasNavigationVisibility;
use App\Filament\Traits\HasAdminOnlyAccess;

class JenisTernakResource extends Resource
{
    use HasNavigationVisibility;
    use HasAdminOnlyAccess;

    protected static ?string $model = JenisTernak::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Jenis Ternak';
    protected static ?string $pluralModelLabel = 'Jenis Ternak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kategori_ternak_id')
                    ->relationship('kategoriTernak', 'nama')
                    ->required(),
                Forms\Components\Select::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->required(),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kategoriTernak.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bidang.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
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
            'index' => Pages\ManageJenisTernaks::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->wewenang->nama === 'Administrator';
    }
}
