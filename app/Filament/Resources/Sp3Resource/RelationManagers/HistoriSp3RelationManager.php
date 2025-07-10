<?php

namespace App\Filament\Resources\Sp3Resource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;

class HistoriSp3RelationManager extends RelationManager
{
    protected static string $relationship = 'historiSp3';
    protected static ?string $title = 'Histori SP3';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Terkirim',
                        'verified_kabupaten' => 'Diverifikasi Kabupaten',
                        'verified_provinsi' => 'Diverifikasi Provinsi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state
                    })
                    ->badge(),
                TextColumn::make('catatan')
                    ->label('Catatan'),
                TextColumn::make('user.name')
                    ->label('Oleh'),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
} 