<?php

namespace App\Filament\Resources\PengajuanResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HistoriPengajuanRelationManager extends RelationManager
{
    protected static string $relationship = 'historiPengajuan';
    protected static ?string $title = 'Histori';
    protected static ?string $icon = 'heroicon-m-clock';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahapVerifikasi.nama')->label('Tahap'),
                Tables\Columns\TextColumn::make('user.name')->label('Verifikator'),
                Tables\Columns\TextColumn::make('status')->badge()->label('Status')
                    ->color(fn(string $state) => match ($state) {
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        'menunggu' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('alasan_penolakan')->label('Alasan Penolakan')->wrap(),
                Tables\Columns\TextColumn::make('catatan')->label('Catatan')->wrap(),
                Tables\Columns\TextColumn::make('created_at')->label('Tanggal')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}