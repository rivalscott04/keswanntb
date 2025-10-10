<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use App\Models\User;
use App\Models\KabKota;
use App\Models\Wewenang;
use Filament\Forms\Form;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

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
                        Forms\Components\Section::make('Bidang Usaha')
                            ->schema([
                                Forms\Components\Select::make('bidang_usaha')
                                    ->label('Bidang Usaha (Komoditas)')
                                    ->options([
                                        'hewan_ternak' => 'Hewan Ternak',
                                        'hewan_kesayangan' => 'Hewan Kesayangan',
                                        'produk_hewan_produk_olahan' => 'Produk Hewan/Produk Olahan',
                                        'gabungan_di_antaranya' => 'Gabungan di Antaranya',
                                    ])
                                    ->required()
                                    ->placeholder('Pilih bidang usaha')
                                    ->helperText('Pilih klasifikasi bidang usaha berdasarkan komoditas yang akan dikelola'),
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
                                Forms\Components\TextInput::make('telepon')
                                    ->label('Telepon/HP/Faximile')
                                    ->unique(User::class, 'telepon')
                                    ->tel(),
                                Forms\Components\FileUpload::make('rekomendasi_keswan')
                                    ->label('Rekomendasi Kab/Kota')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->helperText('Rekomendasi penerbitan SP3 dari Dinas Kabupaten/Kota'),
                                Forms\Components\FileUpload::make('surat_kandang_penampungan')
                                    ->label('Surat Keterangan Mempunyai Kandang Penampungan')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->helperText('Surat Keterangan Mempunyai Kandang Penampungan/Gudang Penyimpanan'),
                                Forms\Components\FileUpload::make('surat_permohonan_perusahaan')
                                    ->label('Surat Permohonan Perusahaan')
                                    ->acceptedFileTypes(['application/pdf']),
                            ])->visible(fn($get) => $get('jenis_akun') === 'perusahaan'),
                        Forms\Components\Section::make('Data Pribadi')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required(),
                                Forms\Components\TextInput::make('nik')
                                    ->label('NIK')
                                    ->required()
                                    ->unique(User::class, 'nik')
                                    ->numeric()
                                    ->length(16),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(User::class, 'email')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->required(),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Ulangi Password')
                                    ->password()
                                    ->required(),
                                Forms\Components\Select::make('kab_kota_id')
                                    ->label('Kabupaten/Kota')
                                    ->options(KabKota::all()->pluck('nama', 'id'))
                                    ->required(),
                                Forms\Components\TextInput::make('desa')
                                    ->label('Desa')
                                    ->required(),
                                Forms\Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->required(),
                                Forms\Components\TextInput::make('no_hp')
                                    ->label('Nomor HP')
                                    ->required()
                                    ->unique(User::class, 'no_hp')
                                    ->tel(),
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
        try {
            $data['wewenang_id'] = Wewenang::where('nama', 'Pengguna')->first()->id;
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
                ->body('Silakan menunggu persetujuan akun Anda.')
                ->send();
            return $user;
        } catch (QueryException $e) {
            // Handle unique constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = $e->getMessage();
                $title = 'Data sudah terdaftar!';
                $body = 'Data yang Anda masukkan sudah terdaftar dalam sistem. Silakan periksa kembali atau gunakan data lain.';
                
                // Check which field caused the duplicate error
                if (str_contains($errorMessage, 'users_email_unique')) {
                    $title = 'Email sudah terdaftar!';
                    $body = 'Email yang Anda gunakan sudah terdaftar dalam sistem. Silakan gunakan email lain atau hubungi administrator.';
                } elseif (str_contains($errorMessage, 'users_nik_unique')) {
                    $title = 'NIK sudah terdaftar!';
                    $body = 'NIK yang Anda masukkan sudah terdaftar dalam sistem. Silakan periksa kembali NIK Anda.';
                } elseif (str_contains($errorMessage, 'users_no_hp_unique')) {
                    $title = 'Nomor HP sudah terdaftar!';
                    $body = 'Nomor HP yang Anda masukkan sudah terdaftar dalam sistem. Silakan gunakan nomor HP lain.';
                } elseif (str_contains($errorMessage, 'users_telepon_unique')) {
                    $title = 'Nomor telepon sudah terdaftar!';
                    $body = 'Nomor telepon yang Anda masukkan sudah terdaftar dalam sistem. Silakan gunakan nomor telepon lain.';
                }
                
                Notification::make()
                    ->title($title)
                    ->danger()
                    ->body($body)
                    ->send();
                
                // Re-throw the exception to prevent form submission
                throw $e;
            }
            
            // Handle other database errors
            Notification::make()
                ->title('Terjadi kesalahan!')
                ->danger()
                ->body('Terjadi kesalahan saat mendaftar. Silakan coba lagi atau hubungi administrator.')
                ->send();
            
            throw $e;
        }
    }
}