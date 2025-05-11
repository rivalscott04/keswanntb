<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait HasNavigationVisibility
{
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // If user is admin, they can see everything
        if ($user->is_admin) {
            return true;
        }

        // If user has wewenang "Pengguna", they can only see Pengajuan group
        if ($user->wewenang->nama === 'Pengguna' && $user->account_verified_at) {
            return static::$navigationGroup === 'Pengajuan';
        }

        // For other wewenang types, they can see everything
        return true;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->wewenang->nama === 'Administrator';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->wewenang->nama === 'Administrator';
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->wewenang->nama === 'Administrator';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->wewenang->nama === 'Administrator';
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }
}