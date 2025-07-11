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
} 