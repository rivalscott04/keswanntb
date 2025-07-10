<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use App\Models\KabKota;

class EditProfile extends BaseEditProfile
{
    public $old_password;
    public $new_password;
    public $new_password_confirmation;

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
                            ->numeric()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('no_hp')
                            ->label('Nomor HP')
                            ->required()
                            ->maxLength(15)
                            ->tel(),
                        Forms\Components\Select::make('kab_kota_id')
                            ->label('Kabupaten/Kota')
                            ->options(KabKota::all()->pluck('nama', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('desa')
                            ->label('Desa/Kelurahan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Ubah Password')
                    ->description('Jika ingin mengubah password, silakan isi password lama dan password baru')
                    ->schema([
                        Forms\Components\TextInput::make('old_password')
                            ->label('Password Lama')
                            ->password()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('new_password')
                            ->label('Password Baru')
                            ->password()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Ulangi Password Baru')
                            ->password()
                            ->same('new_password')
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        // Handle password change
        $oldPassword = $this->old_password;
        $newPassword = $this->new_password;
        $newPasswordConfirmation = $this->new_password_confirmation;

        if ($newPassword) {
            if (!Hash::check($oldPassword, Auth::user()->password)) {
                Notification::make()
                    ->title('Password lama salah!')
                    ->danger()
                    ->send();
                unset($data['password']);
            } elseif ($newPassword !== $newPasswordConfirmation) {
                Notification::make()
                    ->title('Konfirmasi password baru tidak cocok!')
                    ->danger()
                    ->send();
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($newPassword);
            }
        } else {
            unset($data['password']);
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_dashboard')
                ->label('Kembali ke Dashboard')
                ->url(route('filament.admin.pages.dashboard'))
                ->color('gray'),
        ];
    }
}