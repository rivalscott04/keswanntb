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

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->maxLength(16)
                            ->numeric(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->confirmed(),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Ulangi Password')
                            ->password()
                            ->required(),
                        Forms\Components\Select::make('kab_kota_id')
                            ->label('Kabupaten/Kota')
                            ->options(KabKota::all()->pluck('nama', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('desa')
                            ->label('Desa/Kelurahan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('no_hp')
                            ->label('Nomor HP')
                            ->required()
                            ->maxLength(15)
                            ->tel()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function handleRegistration(array $data): User
    {
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
            ->body('Silakan login dan lengkapi data perusahaan untuk membuat pengajuan SP3.')
            ->send();
            
        return $user;
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.auth.login');
    }
}