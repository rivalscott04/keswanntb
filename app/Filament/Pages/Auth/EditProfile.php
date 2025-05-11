<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Status Pendaftaran')
                            ->schema([
                                Forms\Components\Toggle::make('is_pernah_daftar')
                                    ->label('Sudah Pernah Mendaftar?')
                                    ->default(false)
                                    ->live()
                                    ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),
                                Forms\Components\TextInput::make('no_sp3')
                                    ->label('No. SP3')
                                    ->visible(fn($get) => $get('is_pernah_daftar') && auth()->user()->wewenang->nama === 'Pengguna'),
                                Forms\Components\TextInput::make('no_register')
                                    ->label('Nomor Register')
                                    ->visible(fn($get) => $get('is_pernah_daftar') && auth()->user()->wewenang->nama === 'Pengguna'),
                                Forms\Components\FileUpload::make('sp3')
                                    ->label('Dokumen SP3')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->visible(fn($get) => $get('is_pernah_daftar') && auth()->user()->wewenang->nama === 'Pengguna'),
                            ])
                            ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),

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
                                    ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),
                            ])
                            ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),

                        Forms\Components\Section::make('Data Perusahaan/Instansi')
                            ->schema([
                                Forms\Components\TextInput::make('nama_perusahaan')
                                    ->label('Nama Perusahaan/Instansi'),
                                Forms\Components\FileUpload::make('akta_pendirian')
                                    ->label('Akta Pendirian')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\FileUpload::make('surat_domisili')
                                    ->label('Surat Domisili')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\FileUpload::make('surat_izin_usaha')
                                    ->label('Surat Izin Usaha')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\TextInput::make('no_surat_izin_usaha')
                                    ->label('Nomor Surat Izin Usaha'),
                                Forms\Components\FileUpload::make('npwp')
                                    ->label('NPWP')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\TextInput::make('no_npwp')
                                    ->label('Nomor NPWP'),
                                Forms\Components\FileUpload::make('surat_tanda_daftar')
                                    ->label('Tanda Daftar Perusahaan')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\TextInput::make('no_surat_tanda_daftar')
                                    ->label('Nomor Surat Tanda Daftar Perusahaan'),
                                Forms\Components\FileUpload::make('rekomendasi_keswan')
                                    ->label('Rekomendasi Kab/Kota')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\FileUpload::make('surat_kandang_penampungan')
                                    ->label('Surat Keterangan Mempunyai Kandang Penampungan')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\FileUpload::make('surat_permohonan_perusahaan')
                                    ->label('Surat Permohonan Perusahaan')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                            ])
                            ->visible(fn($get) => $get('jenis_akun') === 'perusahaan' && auth()->user()->wewenang->nama === 'Pengguna'),

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
                                    ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),
                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->dehydrated(fn($state) => filled($state))
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state)),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Ulangi Password')
                                    ->password()
                                    ->same('password'),
                                Forms\Components\TextInput::make('desa')
                                    ->label('Desa')
                                    ->required()
                                    ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),
                                Forms\Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->required()
                                    ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),
                                Forms\Components\TextInput::make('telepon')
                                    ->label('Telepon/HP/Faximile')
                                    ->required()
                                    ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),
                            ])
                            ->columns()
                            ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),

                        Forms\Components\Section::make('Dokumen Pendukung')
                            ->schema([
                                Forms\Components\FileUpload::make('dokumen_pendukung')
                                    ->label('Dokumen Pendukung Lainnya')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable(),
                            ])
                            ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),
                    ])
                    ->columnSpanFull()
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Pengguna'),

                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->visible(fn() => auth()->user()->wewenang->nama !== 'Pengguna'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->visible(fn() => auth()->user()->wewenang->nama !== 'Pengguna'),

                        Forms\Components\TextInput::make('no_hp')
                            ->label('No. Telepon')
                            ->required()
                            ->visible(fn() => auth()->user()->wewenang->nama !== 'Pengguna'),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->visible(fn() => auth()->user()->wewenang->nama !== 'Pengguna'),
                    ])
                    ->visible(fn() => auth()->user()->wewenang->nama !== 'Pengguna'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deleteAccount')
                ->label('Hapus Akun')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Hapus Akun')
                ->modalDescription('Apakah Anda yakin ingin menghapus akun Anda? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus Akun')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    $user = Auth::user();

                    // Logout the user
                    Auth::logout();

                    // Delete the user
                    $user->delete();

                    // Redirect to login page
                    $this->redirect('/login');

                    Notification::make()
                        ->title('Akun berhasil dihapus')
                        ->success()
                        ->send();
                }),
        ];
    }
}