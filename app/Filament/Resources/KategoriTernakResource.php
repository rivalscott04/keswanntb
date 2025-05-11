<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriTernakResource\Pages;
use App\Filament\Resources\KategoriTernakResource\RelationManagers;
use App\Models\KategoriTernak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\HasNavigationVisibility;

class KategoriTernakResource extends Resource
{
    use HasNavigationVisibility;

    protected static ?string $model = KategoriTernak::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Kategori Ternak';
    protected static ?string $slug = 'kategori-ternak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi'),
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
                Tables\Actions\EditAction::make()
                    ->modalWidth('2xl'),
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
            'index' => Pages\ManageKategoriTernak::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->wewenang->nama === 'Administrator';
    }
}
