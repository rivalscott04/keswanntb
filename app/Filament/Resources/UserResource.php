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
        return auth()->check() && auth()->user()->is_admin;
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->check() && auth()->user()->is_admin;
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
                                $set('no_nib', null);
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
                                Forms\Components\FileUpload::make('nib')
                                    ->label('NIB (Nomor Induk Berusaha)')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\TextInput::make('no_nib')
                                    ->label('Nomor NIB'),
                                Forms\Components\FileUpload::make('npwp')
                                    ->label('NPWP')
                                    ->acceptedFileTypes(['application/pdf']),
                                Forms\Components\TextInput::make('no_npwp')
                                    ->label('Nomor NPWP'),
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
        $user = auth()->user();
        
        // Filter query berdasarkan wewenang
        $table->modifyQueryUsing(function ($query) use ($user) {
            if ($user->wewenang->nama === 'Disnak Kab/Kota') {
                // Disnak Kab/Kota hanya melihat user dari kab/kota yang sama
                return $query->where('kab_kota_id', $user->kab_kota_id)
                            ->where('wewenang_id', 5); // Pengguna
            }
            if ($user->wewenang->nama === 'Disnak Provinsi') {
                // Disnak Provinsi melihat semua user yang sudah diverifikasi kab/kota
                return $query->where('wewenang_id', 5) // Pengguna
                            ->whereNotNull('kab_kota_verified_at');
            }
            return $query;
        });
        
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
                Tables\Columns\TextColumn::make('kab_kota_verified_at')
                    ->label('Status Kab/Kota')
                    ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'warning')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Kab/Kota' || auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('provinsi_verified_at')
                    ->label('Status Provinsi')
                    ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'warning')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('provinsi_verified_at')
                    ->label('Status Akun')
                    ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Belum Aktif')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
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
                Tables\Filters\SelectFilter::make('kab_kota_verified_at')
                    ->label('Status Verifikasi Kab/Kota')
                    ->options([
                        'verified' => 'Terverifikasi',
                        'unverified' => 'Belum Terverifikasi',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'verified') {
                            return $query->whereNotNull('kab_kota_verified_at');
                        }
                        if ($data['value'] === 'unverified') {
                            return $query->whereNull('kab_kota_verified_at');
                        }
                        return $query;
                    })
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Kab/Kota' || auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Filters\SelectFilter::make('provinsi_verified_at')
                    ->label('Status Verifikasi Provinsi')
                    ->options([
                        'verified' => 'Terverifikasi',
                        'unverified' => 'Belum Terverifikasi',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'verified') {
                            return $query->whereNotNull('provinsi_verified_at');
                        }
                        if ($data['value'] === 'unverified') {
                            return $query->whereNull('provinsi_verified_at');
                        }
                        return $query;
                    })
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
            ])
            ->actions([
                Impersonate::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('verifikasi_kab_kota')
                    ->label('Verifikasi Kab/Kota')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function ($record) {
                        $user = auth()->user();
                        return $user->wewenang->nama === 'Disnak Kab/Kota' &&
                               $user->kab_kota_id === $record->kab_kota_id &&
                               !$record->kab_kota_verified_at &&
                               $record->wewenang->nama === 'Pengguna';
                    })
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Verifikasi')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'kab_kota_verified_at' => now(),
                            'kab_kota_verified_by' => auth()->id(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('User berhasil diverifikasi oleh Kab/Kota')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('verifikasi_provinsi')
                    ->label('Verifikasi Provinsi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function ($record) {
                        $user = auth()->user();
                        return $user->wewenang->nama === 'Disnak Provinsi' &&
                               $record->kab_kota_verified_at &&
                               !$record->provinsi_verified_at &&
                               $record->wewenang->nama === 'Pengguna';
                    })
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Verifikasi')
                            ->required(),
                        Forms\Components\TextInput::make('no_sp3')
                            ->label('No. SP3')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_register')
                            ->label('Nomor Register')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, $record) {
                        $now = now();
                        $record->update([
                            'provinsi_verified_at' => $now,
                            'provinsi_verified_by' => auth()->id(),
                            'tanggal_verifikasi' => $now,
                            'tanggal_berlaku' => $now->copy()->addYears(3),
                            'no_sp3' => $data['no_sp3'],
                            'no_register' => $data['no_register']
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('User berhasil diverifikasi oleh Provinsi')
                            ->success()
                            ->send();
                    }),
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