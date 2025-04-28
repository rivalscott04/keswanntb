<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAdminOnlyAccess
{
    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }
} 