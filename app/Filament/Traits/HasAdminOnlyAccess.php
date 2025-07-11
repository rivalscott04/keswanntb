<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasAdminOnlyAccess
{
    public function mount(): void
    {
        abort_unless(auth()->user()->wewenang->nama === 'Administrator', 403);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->wewenang->nama === 'Administrator';
    }
}