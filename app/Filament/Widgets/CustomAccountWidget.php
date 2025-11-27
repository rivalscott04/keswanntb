<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Support\Enums\ActionSize;

class CustomAccountWidget extends Widget
{
    protected static string $view = 'filament.widgets.custom-account-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 1;

    public function getUser(): \App\Models\User
    {
        return auth()->user();
    }

    protected function getActions(): array
    {
        return [
            Action::make('editProfile')
                ->label('Edit Profil')
                ->icon('heroicon-o-user-circle')
                ->url(route('filament.admin.auth.profile'))
                ->color('primary')
                ->size(ActionSize::Small),
        ];
    }
}

