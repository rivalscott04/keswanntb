<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Pengajuan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('jenis_ternak_id')
                    ->relationship('jenisTernak', 'nama')
                    ->required(),
                Forms\Components\Select::make('provinsi_id')
                    ->relationship('provinsi', 'nama')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('kab_kota_id', null)),
                Forms\Components\Select::make('kab_kota_id')
                    ->relationship('kabKota', 'nama', fn (Builder $query, callable $get) => 
                        $query->when($get('provinsi_id'), fn (Builder $q, $provinsiId) => 
                            $q->where('provinsi_id', $provinsiId)
                        )
                    )
                    ->required()
                    ->disabled(fn (callable $get) => !$get('provinsi_id')),
                Forms\Components\Select::make('tahap_verifikasi_id')
                    ->relationship('tahapVerifikasi', 'nama')
                    ->required(),
                Forms\Components\TextInput::make('nomor_pengajuan')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama_pemohon')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('alamat')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('no_hp')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jumlah_ternak')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Textarea::make('keterangan')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pemohon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenisTernak.nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabKota.nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah_ternak')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahapVerifikasi.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
