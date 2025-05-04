<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KuotaResource\Pages;
use App\Filament\Resources\KuotaResource\RelationManagers;
use App\Filament\Traits\HasAdminOnlyAccess;
use App\Models\Kuota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KuotaResource extends Resource
{
    use HasAdminOnlyAccess;

    protected static ?string $model = Kuota::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Kuota';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'kuota';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jenis_ternak_id')
                    ->label('Jenis Ternak')
                    ->relationship('jenisTernak', 'nama')
                    ->required(),
                Forms\Components\Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'jantan' => 'Jantan',
                        'betina' => 'Betina',
                    ])
                    ->required(),
                Forms\Components\Select::make('pulau')
                    ->label('Pulau')
                    ->options([
                        'Lombok' => 'Lombok',
                        'Sumbawa' => 'Sumbawa',
                    ])
                    ->helperText('Jika memilih pulau, opsi Kab/Kota tidak bisa dipilih')
                    ->live(),
                Forms\Components\Select::make('kab_kota_id')
                    ->label('Kab/Kota')
                    ->relationship('kabKota', 'nama')
                    ->helperText('Jika memilih Kab/Kota, opsi Pulau tidak bisa dipilih')
                    ->disabled(fn(callable $get) => $get('pulau'))
                    ->live(),
                Forms\Components\Select::make('jenis_kuota')
                    ->label('Jenis')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('tahun')
                    ->label('Tahun')
                    ->required()
                    ->numeric()
                    ->minValue(date('Y'))
                    ->default(date('Y')),
                Forms\Components\TextInput::make('kuota')
                    ->label('Jumlah Kuota')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenisTernak.nama')
                    ->label('Jenis Ternak')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pulau')
                    ->label('Pulau')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kabKota.nama')
                    ->label('Kab/Kota')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kuota')
                    ->label('Jenis')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kuota')
                    ->label('Jumlah Kuota')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKuotas::route('/'),
            'create' => Pages\CreateKuota::route('/create'),
            'edit' => Pages\EditKuota::route('/{record}/edit'),
        ];
    }
}
