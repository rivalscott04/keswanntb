<?php

namespace App\Filament\Widgets;

use App\Models\Kuota;
use App\Models\PenggunaanKuota;
use App\Models\KabKota;
use App\Models\JenisTernak;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class KuotaPerKabKotaWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Kuota Per Kab/Kota';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $tahunFilter = $this->getTableFilterState('tahun');
                $jenisTernakFilter = $this->getTableFilterState('jenis_ternak_id');
                $jenisKelaminFilter = $this->getTableFilterState('jenis_kelamin');
                
                // Untuk SelectFilter, nilai biasanya langsung di array atau di ['value']
                $tahun = $tahunFilter && is_array($tahunFilter) && isset($tahunFilter['value']) ? $tahunFilter['value'] : ($tahunFilter && is_numeric($tahunFilter) ? $tahunFilter : now()->year);
                $jenisTernakId = $jenisTernakFilter && is_array($jenisTernakFilter) && isset($jenisTernakFilter['value']) ? $jenisTernakFilter['value'] : ($jenisTernakFilter && is_numeric($jenisTernakFilter) ? $jenisTernakFilter : null);
                $jenisKelamin = $jenisKelaminFilter && is_array($jenisKelaminFilter) && isset($jenisKelaminFilter['value']) ? $jenisKelaminFilter['value'] : ($jenisKelaminFilter && is_string($jenisKelaminFilter) ? $jenisKelaminFilter : null);

                // Ambil semua kab/kota yang memiliki kuota untuk tahun tertentu
                // Termasuk kuota per kab/kota (kab_kota_id tidak null) dan kuota global Lombok (kab_kota_id null, pulau Lombok)
                $kuotaQuery = Kuota::where('tahun', $tahun);
                
                if ($jenisTernakId) {
                    $kuotaQuery->where('jenis_ternak_id', $jenisTernakId);
                }
                if ($jenisKelamin) {
                    $kuotaQuery->where('jenis_kelamin', $jenisKelamin);
                }
                
                // Ambil kab/kota yang memiliki kuota per kab/kota
                $kabKotaIds = $kuotaQuery->whereNotNull('kab_kota_id')
                    ->distinct()
                    ->pluck('kab_kota_id')
                    ->toArray();
                
                // Cek apakah ada kuota global Lombok (untuk pengeluaran)
                $hasLombokGlobalQuota = $kuotaQuery->whereNull('kab_kota_id')
                    ->where('pulau', 'Lombok')
                    ->where('jenis_kuota', 'pengeluaran')
                    ->exists();
                
                // Jika ada kuota global Lombok, tambahkan semua kab/kota Lombok
                $kabKotaLombok = ['Kota Mataram', 'Kab. Lombok Barat', 'Kab. Lombok Tengah', 'Kab. Lombok Timur', 'Kab. Lombok Utara'];
                if ($hasLombokGlobalQuota) {
                    $lombokKabKotaIds = KabKota::whereIn('nama', $kabKotaLombok)->pluck('id')->toArray();
                    $kabKotaIds = array_unique(array_merge($kabKotaIds, $lombokKabKotaIds));
                }

                // Jika ada kab/kota dengan kuota, tampilkan hanya yang memiliki kuota
                // Jika tidak ada, tampilkan semua kab/kota
                $query = KabKota::query();
                
                if (!empty($kabKotaIds)) {
                    $query->whereIn('id', $kabKotaIds);
                }
                
                return $query->orderBy('nama');
            })
            ->columns([
                TextColumn::make('nama')
                    ->label('Kab/Kota')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('kuota_pemasukan')
                    ->label('Kuota Pemasukan')
                    ->state(function ($record) {
                        $tahunFilter = $this->getTableFilterState('tahun');
                        $jenisTernakFilter = $this->getTableFilterState('jenis_ternak_id');
                        $jenisKelaminFilter = $this->getTableFilterState('jenis_kelamin');
                        
                        // Untuk SelectFilter, nilai biasanya langsung di array atau di ['value']
                        $tahun = $tahunFilter && is_array($tahunFilter) && isset($tahunFilter['value']) ? $tahunFilter['value'] : ($tahunFilter && is_numeric($tahunFilter) ? $tahunFilter : now()->year);
                        $jenisTernakId = $jenisTernakFilter && is_array($jenisTernakFilter) && isset($jenisTernakFilter['value']) ? $jenisTernakFilter['value'] : ($jenisTernakFilter && is_numeric($jenisTernakFilter) ? $jenisTernakFilter : null);
                        $jenisKelamin = $jenisKelaminFilter && is_array($jenisKelaminFilter) && isset($jenisKelaminFilter['value']) ? $jenisKelaminFilter['value'] : ($jenisKelaminFilter && is_string($jenisKelaminFilter) ? $jenisKelaminFilter : null);
                        
                        // Untuk pemasukan, pulau menunjukkan asal ternak, bukan tujuan
                        // Jadi kita tidak perlu filter berdasarkan pulau untuk pemasukan
                        $query = Kuota::where('tahun', $tahun)
                            ->where('jenis_kuota', 'pemasukan')
                            ->where('kab_kota_id', $record->id);
                        
                        if ($jenisTernakId) {
                            $query->where('jenis_ternak_id', $jenisTernakId);
                        }
                        if ($jenisKelamin) {
                            $query->where('jenis_kelamin', $jenisKelamin);
                        }
                        
                        $kuota = $query->sum('kuota');
                        return number_format($kuota, 0, ',', '.');
                    })
                    ->alignEnd(),
                
                TextColumn::make('terpakai_pemasukan')
                    ->label('Terpakai (Pemasukan)')
                    ->state(function ($record) {
                        $tahunFilter = $this->getTableFilterState('tahun');
                        $jenisTernakFilter = $this->getTableFilterState('jenis_ternak_id');
                        $jenisKelaminFilter = $this->getTableFilterState('jenis_kelamin');
                        
                        // Untuk SelectFilter, nilai biasanya langsung di array atau di ['value']
                        $tahun = $tahunFilter && is_array($tahunFilter) && isset($tahunFilter['value']) ? $tahunFilter['value'] : ($tahunFilter && is_numeric($tahunFilter) ? $tahunFilter : now()->year);
                        $jenisTernakId = $jenisTernakFilter && is_array($jenisTernakFilter) && isset($jenisTernakFilter['value']) ? $jenisTernakFilter['value'] : ($jenisTernakFilter && is_numeric($jenisTernakFilter) ? $jenisTernakFilter : null);
                        $jenisKelamin = $jenisKelaminFilter && is_array($jenisKelaminFilter) && isset($jenisKelaminFilter['value']) ? $jenisKelaminFilter['value'] : ($jenisKelaminFilter && is_string($jenisKelaminFilter) ? $jenisKelaminFilter : null);
                        
                        // Untuk pemasukan, pulau menunjukkan asal ternak, bukan tujuan
                        // Jadi kita tidak perlu filter berdasarkan pulau untuk pemasukan
                        $query = PenggunaanKuota::where('tahun', $tahun)
                            ->where('jenis_penggunaan', 'pemasukan')
                            ->where('kab_kota_id', $record->id);
                        
                        if ($jenisTernakId) {
                            $query->where('jenis_ternak_id', $jenisTernakId);
                        }
                        if ($jenisKelamin) {
                            $query->where('jenis_kelamin', $jenisKelamin);
                        }
                        
                        $terpakai = $query->sum('jumlah_digunakan');
                        return number_format($terpakai, 0, ',', '.');
                    })
                    ->alignEnd()
                    ->color('warning'),
                
                TextColumn::make('sisa_pemasukan')
                    ->label('Sisa (Pemasukan)')
                    ->state(function ($record) {
                        $tahunFilter = $this->getTableFilterState('tahun');
                        $jenisTernakFilter = $this->getTableFilterState('jenis_ternak_id');
                        $jenisKelaminFilter = $this->getTableFilterState('jenis_kelamin');
                        
                        // Untuk SelectFilter, nilai biasanya langsung di array atau di ['value']
                        $tahun = $tahunFilter && is_array($tahunFilter) && isset($tahunFilter['value']) ? $tahunFilter['value'] : ($tahunFilter && is_numeric($tahunFilter) ? $tahunFilter : now()->year);
                        $jenisTernakId = $jenisTernakFilter && is_array($jenisTernakFilter) && isset($jenisTernakFilter['value']) ? $jenisTernakFilter['value'] : ($jenisTernakFilter && is_numeric($jenisTernakFilter) ? $jenisTernakFilter : null);
                        $jenisKelamin = $jenisKelaminFilter && is_array($jenisKelaminFilter) && isset($jenisKelaminFilter['value']) ? $jenisKelaminFilter['value'] : ($jenisKelaminFilter && is_string($jenisKelaminFilter) ? $jenisKelaminFilter : null);
                        
                        // Untuk pemasukan, pulau menunjukkan asal ternak, bukan tujuan
                        // Jadi kita tidak perlu filter berdasarkan pulau untuk pemasukan
                        $kuotaQuery = Kuota::where('tahun', $tahun)
                            ->where('jenis_kuota', 'pemasukan')
                            ->where('kab_kota_id', $record->id);
                        $terpakaiQuery = PenggunaanKuota::where('tahun', $tahun)
                            ->where('jenis_penggunaan', 'pemasukan')
                            ->where('kab_kota_id', $record->id);
                        
                        if ($jenisTernakId) {
                            $kuotaQuery->where('jenis_ternak_id', $jenisTernakId);
                            $terpakaiQuery->where('jenis_ternak_id', $jenisTernakId);
                        }
                        if ($jenisKelamin) {
                            $kuotaQuery->where('jenis_kelamin', $jenisKelamin);
                            $terpakaiQuery->where('jenis_kelamin', $jenisKelamin);
                        }
                        
                        $kuota = $kuotaQuery->sum('kuota');
                        $terpakai = $terpakaiQuery->sum('jumlah_digunakan');
                        $sisa = max(0, $kuota - $terpakai);
                        return number_format($sisa, 0, ',', '.');
                    })
                    ->alignEnd()
                    ->color('success'),
                
                TextColumn::make('kuota_pengeluaran')
                    ->label('Kuota Pengeluaran')
                    ->state(function ($record) {
                        $tahunFilter = $this->getTableFilterState('tahun');
                        $jenisTernakFilter = $this->getTableFilterState('jenis_ternak_id');
                        $jenisKelaminFilter = $this->getTableFilterState('jenis_kelamin');
                        
                        // Untuk SelectFilter, nilai biasanya langsung di array atau di ['value']
                        $tahun = $tahunFilter && is_array($tahunFilter) && isset($tahunFilter['value']) ? $tahunFilter['value'] : ($tahunFilter && is_numeric($tahunFilter) ? $tahunFilter : now()->year);
                        $jenisTernakId = $jenisTernakFilter && is_array($jenisTernakFilter) && isset($jenisTernakFilter['value']) ? $jenisTernakFilter['value'] : ($jenisTernakFilter && is_numeric($jenisTernakFilter) ? $jenisTernakFilter : null);
                        $jenisKelamin = $jenisKelaminFilter && is_array($jenisKelaminFilter) && isset($jenisKelaminFilter['value']) ? $jenisKelaminFilter['value'] : ($jenisKelaminFilter && is_string($jenisKelaminFilter) ? $jenisKelaminFilter : null);
                        
                        // Untuk Lombok, cek pulau
                        $kabKotaLombok = ['Kota Mataram', 'Kab. Lombok Barat', 'Kab. Lombok Tengah', 'Kab. Lombok Timur', 'Kab. Lombok Utara'];
                        $isLombok = in_array($record->nama ?? '', $kabKotaLombok);
                        
                        if ($isLombok) {
                            // Untuk Lombok, gunakan kuota global (kab_kota_id = null, pulau = 'Lombok')
                            $query = Kuota::where('tahun', $tahun)
                                ->where('jenis_kuota', 'pengeluaran')
                                ->whereNull('kab_kota_id')
                                ->where('pulau', 'Lombok');
                            
                            if ($jenisTernakId) {
                                $query->where('jenis_ternak_id', $jenisTernakId);
                            }
                            if ($jenisKelamin) {
                                $query->where('jenis_kelamin', $jenisKelamin);
                            }
                            
                            $kuota = $query->sum('kuota');
                            return number_format($kuota, 0, ',', '.') . ' *';
                        } else {
                            $query = Kuota::where('tahun', $tahun)
                                ->where('jenis_kuota', 'pengeluaran')
                                ->where('kab_kota_id', $record->id);
                            
                            if ($jenisTernakId) {
                                $query->where('jenis_ternak_id', $jenisTernakId);
                            }
                            if ($jenisKelamin) {
                                $query->where('jenis_kelamin', $jenisKelamin);
                            }
                            
                            $kuota = $query->sum('kuota');
                            return number_format($kuota, 0, ',', '.');
                        }
                    })
                    ->alignEnd(),
                
                TextColumn::make('terpakai_pengeluaran')
                    ->label('Terpakai (Pengeluaran)')
                    ->state(function ($record) {
                        $tahunFilter = $this->getTableFilterState('tahun');
                        $jenisTernakFilter = $this->getTableFilterState('jenis_ternak_id');
                        $jenisKelaminFilter = $this->getTableFilterState('jenis_kelamin');
                        
                        // Untuk SelectFilter, nilai biasanya langsung di array atau di ['value']
                        $tahun = $tahunFilter && is_array($tahunFilter) && isset($tahunFilter['value']) ? $tahunFilter['value'] : ($tahunFilter && is_numeric($tahunFilter) ? $tahunFilter : now()->year);
                        $jenisTernakId = $jenisTernakFilter && is_array($jenisTernakFilter) && isset($jenisTernakFilter['value']) ? $jenisTernakFilter['value'] : ($jenisTernakFilter && is_numeric($jenisTernakFilter) ? $jenisTernakFilter : null);
                        $jenisKelamin = $jenisKelaminFilter && is_array($jenisKelaminFilter) && isset($jenisKelaminFilter['value']) ? $jenisKelaminFilter['value'] : ($jenisKelaminFilter && is_string($jenisKelaminFilter) ? $jenisKelaminFilter : null);
                        
                        $kabKotaLombok = ['Kota Mataram', 'Kab. Lombok Barat', 'Kab. Lombok Tengah', 'Kab. Lombok Timur', 'Kab. Lombok Utara'];
                        $isLombok = in_array($record->nama ?? '', $kabKotaLombok);
                        
                        if ($isLombok) {
                            $query = PenggunaanKuota::where('tahun', $tahun)
                                ->where('jenis_penggunaan', 'pengeluaran')
                                ->where('pulau', 'Lombok');
                            
                            if ($jenisTernakId) {
                                $query->where('jenis_ternak_id', $jenisTernakId);
                            }
                            if ($jenisKelamin) {
                                $query->where('jenis_kelamin', $jenisKelamin);
                            }
                            
                            $terpakai = $query->sum('jumlah_digunakan');
                            return number_format($terpakai, 0, ',', '.') . ' *';
                        } else {
                            $query = PenggunaanKuota::where('tahun', $tahun)
                                ->where('jenis_penggunaan', 'pengeluaran')
                                ->where('kab_kota_id', $record->id);
                            
                            if ($jenisTernakId) {
                                $query->where('jenis_ternak_id', $jenisTernakId);
                            }
                            if ($jenisKelamin) {
                                $query->where('jenis_kelamin', $jenisKelamin);
                            }
                            
                            $terpakai = $query->sum('jumlah_digunakan');
                            return number_format($terpakai, 0, ',', '.');
                        }
                    })
                    ->alignEnd()
                    ->color('warning'),
                
                TextColumn::make('sisa_pengeluaran')
                    ->label('Sisa (Pengeluaran)')
                    ->state(function ($record) {
                        $tahunFilter = $this->getTableFilterState('tahun');
                        $jenisTernakFilter = $this->getTableFilterState('jenis_ternak_id');
                        $jenisKelaminFilter = $this->getTableFilterState('jenis_kelamin');
                        
                        // Untuk SelectFilter, nilai biasanya langsung di array atau di ['value']
                        $tahun = $tahunFilter && is_array($tahunFilter) && isset($tahunFilter['value']) ? $tahunFilter['value'] : ($tahunFilter && is_numeric($tahunFilter) ? $tahunFilter : now()->year);
                        $jenisTernakId = $jenisTernakFilter && is_array($jenisTernakFilter) && isset($jenisTernakFilter['value']) ? $jenisTernakFilter['value'] : ($jenisTernakFilter && is_numeric($jenisTernakFilter) ? $jenisTernakFilter : null);
                        $jenisKelamin = $jenisKelaminFilter && is_array($jenisKelaminFilter) && isset($jenisKelaminFilter['value']) ? $jenisKelaminFilter['value'] : ($jenisKelaminFilter && is_string($jenisKelaminFilter) ? $jenisKelaminFilter : null);
                        
                        $kabKotaLombok = ['Kota Mataram', 'Kab. Lombok Barat', 'Kab. Lombok Tengah', 'Kab. Lombok Timur', 'Kab. Lombok Utara'];
                        $isLombok = in_array($record->nama ?? '', $kabKotaLombok);
                        
                        if ($isLombok) {
                            $kuotaQuery = Kuota::where('tahun', $tahun)
                                ->where('jenis_kuota', 'pengeluaran')
                                ->whereNull('kab_kota_id')
                                ->where('pulau', 'Lombok');
                            $terpakaiQuery = PenggunaanKuota::where('tahun', $tahun)
                                ->where('jenis_penggunaan', 'pengeluaran')
                                ->where('pulau', 'Lombok');
                            
                            if ($jenisTernakId) {
                                $kuotaQuery->where('jenis_ternak_id', $jenisTernakId);
                                $terpakaiQuery->where('jenis_ternak_id', $jenisTernakId);
                            }
                            if ($jenisKelamin) {
                                $kuotaQuery->where('jenis_kelamin', $jenisKelamin);
                                $terpakaiQuery->where('jenis_kelamin', $jenisKelamin);
                            }
                            
                            $kuota = $kuotaQuery->sum('kuota');
                            $terpakai = $terpakaiQuery->sum('jumlah_digunakan');
                            $sisa = max(0, $kuota - $terpakai);
                            return number_format($sisa, 0, ',', '.') . ' *';
                        } else {
                            $kuotaQuery = Kuota::where('tahun', $tahun)
                                ->where('jenis_kuota', 'pengeluaran')
                                ->where('kab_kota_id', $record->id);
                            $terpakaiQuery = PenggunaanKuota::where('tahun', $tahun)
                                ->where('jenis_penggunaan', 'pengeluaran')
                                ->where('kab_kota_id', $record->id);
                            
                            if ($jenisTernakId) {
                                $kuotaQuery->where('jenis_ternak_id', $jenisTernakId);
                                $terpakaiQuery->where('jenis_ternak_id', $jenisTernakId);
                            }
                            if ($jenisKelamin) {
                                $kuotaQuery->where('jenis_kelamin', $jenisKelamin);
                                $terpakaiQuery->where('jenis_kelamin', $jenisKelamin);
                            }
                            
                            $kuota = $kuotaQuery->sum('kuota');
                            $terpakai = $terpakaiQuery->sum('jumlah_digunakan');
                            $sisa = max(0, $kuota - $terpakai);
                            return number_format($sisa, 0, ',', '.');
                        }
                    })
                    ->alignEnd()
                    ->color('success'),
            ])
            ->defaultSort('nama')
            ->paginated([10, 25, 50, 100])
            ->description('* Kuota pengeluaran untuk semua kab/kota di Pulau Lombok (Kota Mataram, Kab. Lombok Barat, Kab. Lombok Tengah, Kab. Lombok Timur, dan Kab. Lombok Utara) digabung menjadi satu.')
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $tahunTersedia = Kuota::select('tahun')
                            ->distinct()
                            ->orderBy('tahun', 'desc')
                            ->pluck('tahun')
                            ->toArray();

                        if (empty($tahunTersedia)) {
                            $currentYear = now()->year;
                            $tahunTersedia = range($currentYear, $currentYear - 4);
                        }

                        return array_combine($tahunTersedia, $tahunTersedia);
                    })
                    ->default(now()->year)
                    ->searchable()
                    ->query(function ($query, $data) {
                        // Jangan terapkan filter langsung ke query karena kolom 'tahun' tidak ada di tabel kab_kota
                        // Filter akan ditangani secara manual di dalam query function
                        return $query;
                    }),
                
                SelectFilter::make('jenis_ternak_id')
                    ->label('Jenis Ternak')
                    ->options(function () {
                        return JenisTernak::orderBy('nama')->pluck('nama', 'id')->toArray();
                    })
                    ->searchable()
                    ->placeholder('Semua Jenis Ternak')
                    ->query(function ($query, $data) {
                        // Jangan terapkan filter langsung ke query karena kolom 'jenis_ternak_id' tidak ada di tabel kab_kota
                        // Filter akan ditangani secara manual di dalam query function
                        return $query;
                    }),
                
                SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'jantan' => 'Jantan',
                        'betina' => 'Betina',
                    ])
                    ->placeholder('Semua Jenis Kelamin')
                    ->query(function ($query, $data) {
                        // Jangan terapkan filter langsung ke query karena kolom 'jenis_kelamin' tidak ada di tabel kab_kota
                        // Filter akan ditangani secara manual di dalam query function
                        return $query;
                    }),
            ])
            ->persistFiltersInSession();
    }
}

