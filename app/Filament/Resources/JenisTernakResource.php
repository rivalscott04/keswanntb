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
                Forms\Components\Section::make('Informasi Jenis Ternak')
                    ->description('Masukkan detail jenis ternak baru.')
                    ->schema([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                        ->schema([
                            Forms\Components\Select::make('kategori_ternak_id')
                                ->relationship('kategoriTernak', 'nama')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('Kategori Ternak'),
                            Forms\Components\Select::make('bidang_id')
                                ->relationship('bidang', 'nama')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('Bidang'),
                            Forms\Components\TextInput::make('nama')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull()
                                ->label('Nama Jenis Ternak')
                                ->placeholder('Contoh: Sapi Bali'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Jenis Ternak')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('kategoriTernak.nama')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->sortable()
                    ->badge()
                    ->color('success'),
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
                Tables\Filters\SelectFilter::make('kategori_ternak_id')
                    ->relationship('kategoriTernak', 'nama')
                    ->label('Kategori Ternak')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->label('Bidang')
                    ->searchable()
                    ->preload(),
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
