<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;

class VerifiedAccountWidget extends Widget
{
    protected static string $view = 'filament.widgets.verified-account-widget';
    
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user->wewenang->nama === 'Pengguna' && $user->provinsi_verified_at && $user->sp3;
    }

    public function getSp3Url(): string
    {
        $user = auth()->user();
        return Storage::url($user->sp3);
    }
}
