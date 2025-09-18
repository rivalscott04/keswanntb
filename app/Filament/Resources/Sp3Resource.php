<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\Sp3Resource\Pages;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;

class Sp3Resource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $label = 'Pengajuan SP3';

    public static function getNavigationLabel(): string
    {
        $user = auth()->user();
        
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            return 'Verifikasi Kab/Kota';
        } elseif ($user->wewenang->nama === 'Disnak Provinsi') {
            return 'Verifikasi Provinsi';
        }
        
        return 'Pengajuan SP3';
    }

    public static function getModelLabel(): string
    {
        $user = auth()->user();
        
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            return 'Pengajuan SP3 Kab/Kota';
        } elseif ($user->wewenang->nama === 'Disnak Provinsi') {
            return 'Pengajuan SP3 Provinsi';
        }
        
        return 'Pengajuan SP3';
    }

    public static function getPluralModelLabel(): string
    {
        $user = auth()->user();
        
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            return 'Pengajuan SP3 Kab/Kota';
        } elseif ($user->wewenang->nama === 'Disnak Provinsi') {
            return 'Pengajuan SP3 Provinsi';
        }
        
        return 'Pengajuan SP3';
    }

    protected static ?string $slug = 'sp3';

    public static function getNavigationDescription(): string
    {
        $user = auth()->user();
        
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            return 'Verifikasi pengajuan SP3 dari perusahaan di wilayah kabupaten/kota Anda';
        } elseif ($user->wewenang->nama === 'Disnak Provinsi') {
            return 'Verifikasi pengajuan SP3 yang sudah diverifikasi oleh kabupaten/kota';
        }
        
        return 'Kelola pengajuan SP3 (Sertifikat Pendaftaran Perusahaan Peternakan)';
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return auth()->check() && (
            $user->is_admin || 
            $user->wewenang->nama === 'Disnak Kab/Kota' || 
            $user->wewenang->nama === 'Disnak Provinsi'
        );
    }

    public static function canCreate(): bool
    {
        return false; // SP3 tidak bisa dibuat manual
    }

    public static function canEdit(Model $record): bool
    {
        return false; // SP3 tidak bisa diedit manual
    }

    public static function canDelete(Model $record): bool
    {
        return false; // SP3 tidak bisa dihapus manual
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()
            ->whereHas('wewenang', function ($query) {
                $query->where('nama', 'Pengguna');
            });

        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            // Disnak Kab/Kota hanya melihat user dari kab/kota yang sama
            return $query->where('kab_kota_id', $user->kab_kota_id);
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabKota.nama')
                    ->label('Kab/Kota')
                    ->searchable()
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('bidang_usaha')
                    ->label('Bidang Usaha')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'hewan_ternak' => 'Hewan Ternak',
                        'hewan_kesayangan' => 'Hewan Kesayangan',
                        'produk_hewan_produk_olahan' => 'Produk Hewan/Produk Olahan',
                        'gabungan_di_antaranya' => 'Gabungan di Antaranya',
                        default => '-'
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('kab_kota_verified_at')
                    ->label('Status Kab/Kota')
                    ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'warning')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Kab/Kota' || auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('provinsi_verified_at')
                    ->label('Status Provinsi')
                    ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'warning')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('kab_kota_verified_at')
                    ->label('Tanggal Verifikasi Kab/Kota')
                    ->dateTime('d/m/Y H:i')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('provinsi_verified_at')
                    ->label('Tanggal Verifikasi Provinsi')
                    ->dateTime('d/m/Y H:i')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('no_sp3')
                    ->label('No. SP3')
                    ->searchable()
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('no_register')
                    ->label('No. Register')
                    ->searchable()
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('tanggal_berlaku')
                    ->label('Tanggal Berlaku')
                    ->date('d/m/Y')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status SP3')
                    ->formatStateUsing(function ($record) {
                        if (!$record->no_sp3) return 'Belum Ada';
                        return $record->tanggal_berlaku > now() ? 'Aktif' : 'Kadaluarsa';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->no_sp3) return 'gray';
                        return $record->tanggal_berlaku > now() ? 'success' : 'danger';
                    }),
            ])
            ->filters([
                Filter::make('unverified_kab_kota')
                    ->label('Belum Terverifikasi Kab/Kota')
                    ->query(fn(Builder $query): Builder => $query->whereNull('kab_kota_verified_at'))
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Kab/Kota')
                    ->default(),
                Filter::make('unverified_provinsi')
                    ->label('Belum Terverifikasi Provinsi')
                    ->query(fn(Builder $query): Builder => $query->whereNull('provinsi_verified_at'))
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi')
                    ->default(),
                Filter::make('verified_kab_kota')
                    ->label('Sudah Terverifikasi Kab/Kota')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('kab_kota_verified_at'))
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Kab/Kota'),
                Filter::make('verified_provinsi')
                    ->label('Sudah Terverifikasi Provinsi')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('provinsi_verified_at'))
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                SelectFilter::make('kab_kota_id')
                    ->label('Kab/Kota')
                    ->relationship('kabKota', 'nama')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Filter::make('sp3_aktif')
                    ->label('SP3 Aktif')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('no_sp3')->where('tanggal_berlaku', '>', now()))
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
                Filter::make('sp3_kadaluarsa')
                    ->label('SP3 Kadaluarsa')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('no_sp3')->where('tanggal_berlaku', '<=', now()))
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('verifikasi_kab_kota')
                    ->label('Verifikasi Kab/Kota')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function ($record) {
                        $user = auth()->user();
                        return $user->wewenang->nama === 'Disnak Kab/Kota' &&
                               $user->kab_kota_id === $record->kab_kota_id &&
                               !$record->kab_kota_verified_at;
                    })
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Verifikasi')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'kab_kota_verified_at' => now(),
                            'kab_kota_verified_by' => auth()->id(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('User berhasil diverifikasi oleh Kab/Kota')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('verifikasi_provinsi')
                    ->label('Verifikasi Provinsi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function ($record) {
                        $user = auth()->user();
                        return $user->wewenang->nama === 'Disnak Provinsi' &&
                               $record->kab_kota_verified_at &&
                               !$record->provinsi_verified_at;
                    })
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Verifikasi')
                            ->required(),
                        Forms\Components\TextInput::make('no_sp3')
                            ->label('No. SP3')
                            ->required(fn($record) => !$record->is_pernah_daftar)
                            ->default(fn($record) => $record->no_sp3)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_register')
                            ->label('Nomor Register')
                            ->required(fn($record) => !$record->is_pernah_daftar)
                            ->default(fn($record) => $record->no_register)
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('sp3')
                            ->label('Dokumen SP3')
                            ->required(fn($record) => !$record->is_pernah_daftar)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240) // 10MB
                            ->directory('sp3-documents')
                            ->visibility('private')
                            ->downloadable()
                            ->openable()
                            ->previewable(false)
                            ->helperText('Upload dokumen SP3 yang sudah ditandatangani (PDF, JPG, PNG - maksimal 10MB)'),
                    ])
                    ->action(function (array $data, $record) {
                        $now = now();
                        $updateData = [
                            'provinsi_verified_at' => $now,
                            'provinsi_verified_by' => auth()->id(),
                            'tanggal_verifikasi' => $now,
                            'tanggal_berlaku' => $now->copy()->addYears(3),
                            'no_sp3' => $data['no_sp3'],
                            'no_register' => $data['no_register']
                        ];

                        // Only update SP3 document if it's a new registration or if a new document is uploaded
                        if (!$record->is_pernah_daftar || !empty($data['sp3'])) {
                            $updateData['sp3'] = $data['sp3'];
                        }

                        $record->update($updateData);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('User berhasil diverifikasi oleh Provinsi')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('lihat_sp3')
                    ->label('Lihat SP3')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(function ($record) {
                        return $record->provinsi_verified_at && $record->no_sp3 && $record->sp3;
                    })
                    ->url(fn($record) => \Illuminate\Support\Facades\Storage::url($record->sp3))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('perpanjang_sp3')
                    ->label('Perpanjang SP3')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(function ($record) {
                        $user = auth()->user();
                        return $user->wewenang->nama === 'Disnak Provinsi' &&
                               $record->provinsi_verified_at &&
                               $record->no_sp3;
                    })
                    ->form([
                        Forms\Components\TextInput::make('no_sp3_baru')
                            ->label('No. SP3 Baru')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_register_baru')
                            ->label('Nomor Register Baru')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Perpanjangan')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        $now = now();
                        $record->update([
                            'no_sp3' => $data['no_sp3_baru'],
                            'no_register' => $data['no_register_baru'],
                            'tanggal_verifikasi' => $now,
                            'tanggal_berlaku' => $now->copy()->addYears(3),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('SP3 berhasil diperpanjang')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('verifikasi_kab_kota_bulk')
                    ->label('Verifikasi Kab/Kota (Terpilih)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Kab/Kota')
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Verifikasi')
                            ->required(),
                    ])
                    ->action(function (array $data, $records) {
                        $records->each(function ($record) use ($data) {
                            $record->update([
                                'kab_kota_verified_at' => now(),
                                'kab_kota_verified_by' => auth()->id(),
                            ]);
                        });
                        
                        \Filament\Notifications\Notification::make()
                            ->title(count($records) . ' user berhasil diverifikasi oleh Kab/Kota')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\BulkAction::make('verifikasi_provinsi_bulk')
                    ->label('Verifikasi Provinsi (Terpilih)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn() => auth()->user()->wewenang->nama === 'Disnak Provinsi')
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Verifikasi')
                            ->required(),
                        Forms\Components\TextInput::make('no_sp3_prefix')
                            ->label('Prefix No. SP3')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('no_register_prefix')
                            ->label('Prefix No. Register')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\FileUpload::make('sp3_template')
                            ->label('Template Dokumen SP3')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240) // 10MB
                            ->directory('sp3-documents')
                            ->visibility('private')
                            ->helperText('Upload template dokumen SP3 yang sudah ditandatangani (PDF, JPG, PNG - maksimal 10MB)'),
                    ])
                    ->action(function (array $data, $records) {
                        $counter = 1;
                        $records->each(function ($record) use ($data, &$counter) {
                            $now = now();
                            $updateData = [
                                'provinsi_verified_at' => $now,
                                'provinsi_verified_by' => auth()->id(),
                                'tanggal_verifikasi' => $now,
                                'tanggal_berlaku' => $now->copy()->addYears(3),
                                'no_sp3' => $data['no_sp3_prefix'] . '/' . str_pad($counter, 3, '0', STR_PAD_LEFT) . '/' . $now->format('Y'),
                                'no_register' => $data['no_register_prefix'] . '/' . str_pad($counter, 3, '0', STR_PAD_LEFT) . '/' . $now->format('Y'),
                            ];

                            // Only update SP3 document if it's a new registration or if a template is provided
                            if (!$record->is_pernah_daftar || !empty($data['sp3_template'])) {
                                $updateData['sp3'] = $data['sp3_template'];
                            }

                            $record->update($updateData);
                            $counter++;
                        });
                        
                        \Filament\Notifications\Notification::make()
                            ->title(count($records) . ' user berhasil diverifikasi oleh Provinsi')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('refresh_pelabuhan_cache')
                    ->label('Refresh Data Pelabuhan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn() => auth()->user()->is_admin)
                    ->action(function () {
                        try {
                            // Show loading notification
                            \Filament\Notifications\Notification::make()
                                ->title('Memuat data pelabuhan...')
                                ->body('Sedang mengambil data dari API Kemenhub')
                                ->info()
                                ->send();
                            
                            $result = \App\Services\PelabuhanService::refreshCache();
                            
                            if ($result['success']) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Cache data pelabuhan berhasil diperbarui')
                                    ->body($result['message'] . '. Terakhir diperbarui: ' . ($result['last_updated'] ? $result['last_updated']->format('d/m/Y H:i') : 'Belum pernah'))
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal memperbarui cache data pelabuhan')
                                    ->body('Error: ' . $result['error'])
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal memperbarui cache data pelabuhan')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSp3::route('/'),
            'view' => Pages\ViewSp3::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            $count = User::where('wewenang_id', 5) // Pengguna
                ->where('kab_kota_id', $user->kab_kota_id)
                ->whereNull('kab_kota_verified_at')
                ->count();
        } elseif ($user->wewenang->nama === 'Disnak Provinsi') {
            $count = User::where('wewenang_id', 5) // Pengguna
                ->whereNotNull('kab_kota_verified_at')
                ->whereNull('provinsi_verified_at')
                ->count();
        } else {
            return null;
        }
        
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = auth()->user();
        
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            $count = User::where('wewenang_id', 5) // Pengguna
                ->where('kab_kota_id', $user->kab_kota_id)
                ->whereNull('kab_kota_verified_at')
                ->count();
        } elseif ($user->wewenang->nama === 'Disnak Provinsi') {
            $count = User::where('wewenang_id', 5) // Pengguna
                ->whereNotNull('kab_kota_verified_at')
                ->whereNull('provinsi_verified_at')
                ->count();
        } else {
            return null;
        }
        
        return $count > 0 ? 'warning' : null;
    }
}