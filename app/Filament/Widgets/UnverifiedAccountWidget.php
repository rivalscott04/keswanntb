<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Support\Enums\ActionSize;

class UnverifiedAccountWidget extends Widget
{
    protected static string $view = 'filament.widgets.unverified-account-widget';
    
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user->wewenang->nama === 'Pengguna' && !$user->provinsi_verified_at;
    }

    public function getVerificationStatus(): array
    {
        $user = auth()->user();
        
        if (!$user->kab_kota_verified_at) {
            return [
                'status' => 'kab_kota',
                'message' => 'Sedang menunggu konfirmasi dari kab/kota',
                'description' => 'Maaf, akun Anda belum diverifikasi oleh kab/kota. Silakan lengkapi dan perbarui dokumen SP3 Anda untuk dapat melakukan pengajuan. Untuk informasi lebih lanjut, silakan hubungi Disnak Kab/Kota setempat.'
            ];
        } elseif (!$user->provinsi_verified_at) {
            return [
                'status' => 'provinsi',
                'message' => 'Sedang menunggu konfirmasi dari provinsi',
                'description' => 'Maaf, akun Anda belum diverifikasi oleh provinsi. Silakan lengkapi dan perbarui dokumen SP3 Anda untuk dapat melakukan pengajuan. Untuk informasi lebih lanjut, silakan hubungi Disnak Provinsi NTB.'
            ];
        }
        
        return [
            'status' => 'verified',
            'message' => 'Akun Terverifikasi',
            'description' => 'Akun Anda telah diverifikasi dan dapat melakukan pengajuan.'
        ];
    }
} 