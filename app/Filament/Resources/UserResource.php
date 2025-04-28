<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Pengaturan';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jenis_akun')
                    ->label('Jenis Akun')
                    ->options([
                        'perusahaan' => 'Perusahaan',
                        'perorangan' => 'Perorangan/Instansi Pemerintah',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn($context) => $context === 'create'),
                Forms\Components\TextInput::make('no_sp3')
                    ->label('No. SP3')
                    ->visible(fn(callable $get) => $get('is_pernah_daftar')),
                Forms\Components\TextInput::make('no_register')
                    ->label('Nomor Register')
                    ->visible(fn(callable $get) => $get('is_pernah_daftar')),
                Forms\Components\FileUpload::make('sp3')
                    ->label('Dokumen SP3')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('is_pernah_daftar')),
                Forms\Components\Toggle::make('is_pernah_daftar')
                    ->label('Sudah Pernah Mendaftar?'),
                Forms\Components\FileUpload::make('akta_pendirian')
                    ->label('Akta Pendirian')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\FileUpload::make('surat_domisili')
                    ->label('Surat Domisili')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\FileUpload::make('surat_izin_usaha')
                    ->label('Surat Izin Usaha')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\TextInput::make('no_surat_izin_usaha')
                    ->label('Nomor Surat Izin Usaha')
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\TextInput::make('nama_perusahaan')
                    ->label('Nama Perusahaan/Instansi')
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\FileUpload::make('npwp')
                    ->label('NPWP')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\TextInput::make('no_npwp')
                    ->label('Nomor NPWP')
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\FileUpload::make('surat_tanda_daftar')
                    ->label('Tanda Daftar Perusahaan')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\TextInput::make('no_surat_tanda_daftar')
                    ->label('Nomor Surat Tanda Daftar Perusahaan')
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\FileUpload::make('rekomendasi_keswan')
                    ->label('Rekomendasi Kab/Kota')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\FileUpload::make('surat_kandang_penampungan')
                    ->label('Surat Keterangan Mempunyai Kandang Penampungan')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\FileUpload::make('dokumen_pendukung')
                    ->label('Dokumen Pendukung Lainnya')
                    ->acceptedFileTypes(['application/pdf']),
                Forms\Components\FileUpload::make('surat_permohonan_perusahaan')
                    ->label('Surat Permohonan Perusahaan')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn(callable $get) => $get('jenis_akun') === 'perusahaan'),
                Forms\Components\TextInput::make('telepon')
                    ->label('Telepon/HP/Faximile'),
                Forms\Components\Toggle::make('status')
                    ->label('Status'),
                Forms\Components\DatePicker::make('tanggal_verifikasi')
                    ->label('Tanggal Verifikasi'),
                Forms\Components\DatePicker::make('tanggal_berlaku')
                    ->label('Tanggal Berlaku'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabKota.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('wewenang.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bidang.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_hp')
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
                Impersonate::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}