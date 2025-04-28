<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Form;

class Register extends BaseRegister
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
                                    ->live(),
                                Forms\Components\TextInput::make('no_sp3')
                                    ->label('No. SP3')
                                    ->visible(fn($get) => $get('is_pernah_daftar')),
                                Forms\Components\TextInput::make('no_register')
                                    ->label('Nomor Register')
                                    ->visible(fn($get) => $get('is_pernah_daftar')),
                                Forms\Components\FileUpload::make('sp3')
                                    ->label('Dokumen SP3')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->visible(fn($get) => $get('is_pernah_daftar')),
                            ])
                            ->live(),
                        Forms\Components\Section::make('Jenis Akun')
                            ->schema([
                                Forms\Components\Select::make('jenis_akun')
                                    ->label('Jenis Akun')
                                    ->options([
                                        'perusahaan' => 'Perusahaan',
                                        'perorangan' => 'Perorangan/Instansi Pemerintah',
                                    ])
                                    ->required()
                                    ->live(),
                            ]),
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
                            ])->visible(fn($get) => $get('jenis_akun') === 'perusahaan'),
                        Forms\Components\Section::make('Data Pribadi')
                            ->schema([
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
                                    ->required(),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Ulangi Password')
                                    ->password()
                                    ->required(),
                                Forms\Components\TextInput::make('desa')
                                    ->label('Desa')
                                    ->required(),
                                Forms\Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->required(),
                                Forms\Components\TextInput::make('telepon')
                                    ->label('Telepon/HP/Faximile')
                                    ->required(),
                            ])->columns(),
                        Forms\Components\Section::make('Dokumen Pendukung')
                            ->schema([
                                Forms\Components\FileUpload::make('dokumen_pendukung')
                                    ->label('Dokumen Pendukung Lainnya')
                                    ->acceptedFileTypes(['application/pdf']),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function handleRegistration(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        unset($data['password_confirmation']);
        if (App::environment('local')) {
            $data['email_verified_at'] = now();
        }
        $user = User::create($data);
        if (App::environment('production')) {
            event(new Registered($user));
        }
        Notification::make()
            ->title('Pendaftaran berhasil!')
            ->success()
            ->body('Silakan login menggunakan email dan password Anda.')
            ->send();
        return $user;
    }
}