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
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $label = 'Pengguna';

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
                Forms\Components\Section::make('Informasi Wewenang')
                    ->schema([
                        Forms\Components\Select::make('wewenang_id')
                            ->label('Wewenang')
                            ->relationship('wewenang', 'nama')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset fields when wewenang changes
                                $set('bidang_id', null);
                                $set('kab_kota_id', null);
                                $set('company_name', null);
                                $set('company_address', null);
                                $set('npwp', null);
                                $set('no_surat_izin_usaha', null);
                                $set('no_surat_tanda_daftar', null);
                                $set('no_register', null);
                            }),

                        Forms\Components\Select::make('bidang_id')
                            ->label('Bidang')
                            ->relationship('bidang', 'nama')
                            ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [2])) // Disnak Provinsi
                            ->required(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [2])),

                        Forms\Components\Select::make('kab_kota_id')
                            ->label('Kabupaten/Kota')
                            ->relationship('kabKota', 'nama')
                            ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [3])) // Disnak Kab/Kota
                            ->required(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [3])),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Status Pendaftaran')
                            ->schema([
                                Forms\Components\Toggle::make('is_pernah_daftar')
                                    ->label('Sudah Pernah Mendaftar?')
                                    ->default(false)
                                    ->live()
                                    ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                                Forms\Components\TextInput::make('no_sp3')
                                    ->label('No. SP3')
                                    ->visible(fn($get) => $get('is_pernah_daftar') && $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                                Forms\Components\TextInput::make('no_register')
                                    ->label('Nomor Register')
                                    ->visible(fn($get) => $get('is_pernah_daftar') && $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                                Forms\Components\FileUpload::make('sp3')
                                    ->label('Dokumen SP3')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->visible(fn($get) => $get('is_pernah_daftar') && $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                            ])
                            ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna

                        Forms\Components\Section::make('Jenis Akun')
                            ->schema([
                                Forms\Components\Select::make('jenis_akun')
                                    ->label('Jenis Akun')
                                    ->options([
                                        'perusahaan' => 'Perusahaan',
                                        'perorangan' => 'Perorangan/Instansi Pemerintah',
                                    ])
                                    ->required()
                                    ->live()
                                    ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                            ])
                            ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna

                        Forms\Components\Section::make('Data Perusahaan/Instansi')
                            ->schema([
                                Forms\Components\TextInput::make('nama_perusahaan')
                                    ->label('Nama Perusahaan/Instansi'),
                                Forms\Components\FileUpload::make('akta_pendirian')
                                    ->label('Akta Pendirian')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\FileUpload::make('surat_domisili')
                                    ->label('Surat Domisili')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\FileUpload::make('surat_izin_usaha')
                                    ->label('Surat Izin Usaha')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\TextInput::make('no_surat_izin_usaha')
                                    ->label('Nomor Surat Izin Usaha'),
                                Forms\Components\FileUpload::make('npwp')
                                    ->label('NPWP')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\TextInput::make('no_npwp')
                                    ->label('Nomor NPWP'),
                                Forms\Components\FileUpload::make('surat_tanda_daftar')
                                    ->label('Tanda Daftar Perusahaan')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\TextInput::make('no_surat_tanda_daftar')
                                    ->label('Nomor Surat Tanda Daftar Perusahaan'),
                                Forms\Components\FileUpload::make('rekomendasi_keswan')
                                    ->label('Rekomendasi Kab/Kota')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\FileUpload::make('surat_kandang_penampungan')
                                    ->label('Surat Keterangan Mempunyai Kandang Penampungan')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\FileUpload::make('surat_permohonan_perusahaan')
                                    ->label('Surat Permohonan Perusahaan')
                                    ->acceptedFileTypes(['application/pdf']),
                            ])
                            ->visible(fn($get) => $get('jenis_akun') === 'perusahaan' && $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna

                        Forms\Components\Section::make('Data Pribadi')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('nik')
                                    ->label('NIK')
                                    ->required()
                                    ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Ulangi Password')
                                    ->password()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->same('password')
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('desa')
                                    ->label('Desa')
                                    ->required()
                                    ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                                Forms\Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->required()
                                    ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                                Forms\Components\TextInput::make('telepon')
                                    ->label('Telepon/HP/Faximile')
                                    ->required()
                                    ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                            ])
                            ->columns()
                            ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna

                        Forms\Components\Section::make('Dokumen Pendukung')
                            ->schema([
                                Forms\Components\FileUpload::make('dokumen_pendukung')
                                    ->label('Dokumen Pendukung Lainnya')
                                    ->acceptedFileTypes(['application/pdf']),
                            ])
                            ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna
                    ])
                    ->columnSpanFull()
                    ->visible(fn (callable $get) => $get('wewenang_id') && in_array($get('wewenang_id'), [5])), // Pengguna

                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->visible(fn (callable $get) => !$get('wewenang_id') || !in_array($get('wewenang_id'), [5])),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->visible(fn (callable $get) => !$get('wewenang_id') || !in_array($get('wewenang_id'), [5])),

                        Forms\Components\TextInput::make('no_hp')
                            ->label('No. Telepon')
                            ->required()
                            ->visible(fn (callable $get) => !$get('wewenang_id') || !in_array($get('wewenang_id'), [5])),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->visible(fn (callable $get) => !$get('wewenang_id') || !in_array($get('wewenang_id'), [5])),
                    ])
                    ->visible(fn (callable $get) => !$get('wewenang_id') || !in_array($get('wewenang_id'), [5])),
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
                Tables\Filters\SelectFilter::make('wewenang')
                    ->label('Jenis Wewenang')
                    ->multiple()
                    ->relationship('wewenang', 'nama')
                    ->preload(),
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