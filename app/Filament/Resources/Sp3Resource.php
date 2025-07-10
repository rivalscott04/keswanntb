<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp3Resource\Pages;
use App\Filament\Resources\Sp3Resource\RelationManagers;
use App\Models\Sp3;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class Sp3Resource extends Resource
{
    protected static ?string $model = Sp3::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $label = 'Pengajuan SP3';

    protected static ?string $slug = 'sp3';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::check();
    }

    public static function canCreate(): bool
    {
        return Auth::check() && !Auth::user()->is_admin;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::check() && (Auth::user()->is_admin || $record->user_id === Auth::id());
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()->is_admin) {
            return parent::getEloquentQuery();
        }
        
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Perusahaan')
                    ->schema([
                        Forms\Components\Select::make('jenis_akun')
                            ->label('Jenis Akun')
                            ->options([
                                'perusahaan' => 'Perusahaan',
                                'perorangan' => 'Perorangan/Instansi Pemerintah',
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('nama_perusahaan')
                            ->label('Nama Perusahaan/Instansi')
                            ->required()
                            ->visible(fn ($get) => $get('jenis_akun') === 'perusahaan'),
                        
                        Forms\Components\Select::make('bidang_usaha')
                            ->label('Bidang Usaha')
                            ->options([
                                'hewan_ternak' => 'Hewan Ternak',
                                'hewan_kesayangan' => 'Hewan Kesayangan',
                                'produk_hewan_produk_olahan' => 'Produk Hewan/Produk Olahan',
                                'gabungan_di_antaranya' => 'Gabungan di Antaranya',
                            ])
                            ->required()
                            ->visible(fn ($get) => $get('jenis_akun') === 'perusahaan'),
                        
                        Forms\Components\FileUpload::make('akta_pendirian')
                            ->label('Akta Pendirian')
                            ->directory('sp3/akta_pendirian')
                            ->visible(fn ($get) => $get('jenis_akun') === 'perusahaan'),
                        
                        Forms\Components\FileUpload::make('surat_domisili')
                            ->label('Surat Domisili')
                            ->directory('sp3/surat_domisili')
                            ->visible(fn ($get) => $get('jenis_akun') === 'perusahaan'),
                        
                        Forms\Components\TextInput::make('no_nib')
                            ->label('Nomor NIB')
                            ->visible(fn ($get) => $get('jenis_akun') === 'perusahaan'),
                        
                        Forms\Components\FileUpload::make('nib')
                            ->label('NIB (Nomor Induk Berusaha)')
                            ->directory('sp3/nib')
                            ->visible(fn ($get) => $get('jenis_akun') === 'perusahaan'),
                        
                        Forms\Components\TextInput::make('no_npwp')
                            ->label('Nomor NPWP')
                            ->visible(fn ($get) => $get('jenis_akun') === 'perusahaan'),
                        
                        Forms\Components\FileUpload::make('npwp')
                            ->label('NPWP')
                            ->directory('sp3/npwp')
                            ->visible(fn ($get) => $get('jenis_akun') === 'perusahaan'),
                        
                        // Forms\Components\FileUpload::make('rekomendasi_keswan')
                        //     ->label('Rekomendasi Kab/Kota')
                        //     ->directory('sp3/rekomendasi_keswan')
                        //     ->required(),
                        
                        // Forms\Components\FileUpload::make('surat_kandang_penampungan')
                        //     ->label('Surat Keterangan Mempunyai Kandang Penampungan')
                        //     ->directory('sp3/surat_kandang_penampungan')
                        //     ->required(),
                        
                        // Forms\Components\FileUpload::make('surat_permohonan_perusahaan')
                        //     ->label('Surat Permohonan Perusahaan')
                        //     ->directory('sp3/surat_permohonan_perusahaan')
                        //     ->required(),
                        
                        Forms\Components\FileUpload::make('dokumen_pendukung')
                            ->label('Dokumen Pendukung Lainnya')
                            ->directory('sp3/dokumen_pendukung')
                            ->columnSpanFull(),
                    ])->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pemohon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bidang_usaha')
                    ->label('Bidang Usaha')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'hewan_ternak' => 'Hewan Ternak',
                        'hewan_kesayangan' => 'Hewan Kesayangan',
                        'produk_hewan_produk_olahan' => 'Produk Hewan/Produk Olahan',
                        'gabungan_di_antaranya' => 'Gabungan di Antaranya',
                        default => '-'
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Terkirim',
                        'verified_kabupaten' => 'Diverifikasi Kabupaten',
                        'verified_provinsi' => 'Diverifikasi Provinsi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => '-'
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'info',
                        'verified_kabupaten' => 'warning',
                        'verified_provinsi' => 'primary',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Terkirim',
                        'verified_kabupaten' => 'Diverifikasi Kabupaten',
                        'verified_provinsi' => 'Diverifikasi Provinsi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->status === 'draft'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoriSp3RelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSp3s::route('/'),
            'create' => Pages\CreateSp3::route('/create'),
            'edit' => Pages\EditSp3::route('/{record}/edit'),
            'view' => Pages\ViewSp3::route('/{record}'),
        ];
    }
}
