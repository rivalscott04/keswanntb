<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\JenisTernak;
use App\Models\KabKota;
use App\Models\Provinsi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportService
{
    public static function generateReport($tanggalMulai, $tanggalAkhir, $jenisTernakIds = [], $jenisPengajuan = null)
    {
        $query = Pengajuan::with([
            'user',
            'jenisTernak.kategoriTernak',
            'kabKotaAsal',
            'kabKotaTujuan',
            'provinsiAsal',
            'provinsiTujuan'
        ])
            ->where(function ($q) use ($tanggalMulai, $tanggalAkhir) {
                $q->whereBetween('tanggal_surat_permohonan', [$tanggalMulai, $tanggalAkhir])
                  ->orWhere(function ($subQ) use ($tanggalMulai, $tanggalAkhir) {
                      $subQ->whereNull('tanggal_surat_permohonan')
                           ->whereBetween('created_at', [$tanggalMulai, $tanggalAkhir]);
                  });
            })
            ->when(!empty($jenisTernakIds), function ($q) use ($jenisTernakIds) {
                return $q->whereIn('jenis_ternak_id', $jenisTernakIds);
            })
            ->when(!empty($jenisPengajuan), function ($q) use ($jenisPengajuan) {
                return $q->where('jenis_pengajuan', $jenisPengajuan);
            });

        $data = $query->get();

        $fileName = 'Laporan_Pengajuan_Ternak_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new ReportExport($data, $tanggalMulai, $tanggalAkhir), $fileName);
    }
}

class ReportExport implements WithMultipleSheets
{
    protected $data;
    protected $tanggalMulai;
    protected $tanggalAkhir;

    public function __construct($data, $tanggalMulai, $tanggalAkhir)
    {
        $this->data = $data;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Sheet 1: Ringkasan Eksekutif
        $sheets[] = new SummarySheet($this->data, $this->tanggalMulai, $this->tanggalAkhir);
        
        // Sheet 2: Detail Transaksi
        $sheets[] = new DetailTransactionSheet($this->data);
        
        // Sheet 3: Antar Kab/Kota
        $sheets[] = new AntarKabKotaSheet($this->data);
        
        // Sheet 4: Pengeluaran
        $sheets[] = new PengeluaranSheet($this->data);
        
        // Sheet 5: Pemasukan
        $sheets[] = new PemasukanSheet($this->data);
        
        // Sheet 6: Analisis Kuota
        $sheets[] = new AnalisisKuotaSheet($this->data);
        
        // Sheet 7: Breakdown Perusahaan
        $sheets[] = new BreakdownPerusahaanSheet($this->data);
        
        return $sheets;
    }
}

// Sheet 1: Ringkasan Eksekutif
class SummarySheet implements FromCollection, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;
    protected $tanggalMulai;
    protected $tanggalAkhir;

    public function __construct($data, $tanggalMulai, $tanggalAkhir)
    {
        $this->data = $data;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function collection()
    {
        // Pre-load semua KabKota yang dibutuhkan untuk menghindari N+1 query
        $kabKotaIds = $this->data->pluck('kab_kota_asal_id')
            ->merge($this->data->pluck('kab_kota_tujuan_id'))
            ->filter()
            ->unique();
        
        $kabKotaMap = KabKota::whereIn('id', $kabKotaIds)->pluck('nama', 'id');
        
        $summary = [];
        
        // Total Pengajuan
        $summary[] = ['Keterangan', 'Jumlah'];
        $summary[] = ['Total Pengajuan', $this->data->count()];
        $summary[] = ['Total Jumlah Ternak', $this->data->sum('jumlah_ternak')];
        $summary[] = [];
        
        // Breakdown Jenis Pengajuan
        $summary[] = ['BREAKDOWN JENIS PENGAJUAN', ''];
        $antarKabKota = $this->data->where('jenis_pengajuan', 'antar_kab_kota');
        $pengeluaran = $this->data->where('jenis_pengajuan', 'pengeluaran');
        $pemasukan = $this->data->where('jenis_pengajuan', 'pemasukan');
        
        $summary[] = ['Antar Kab/Kota', $antarKabKota->count() . ' pengajuan, ' . $antarKabKota->sum('jumlah_ternak') . ' ekor'];
        $summary[] = ['Pengeluaran', $pengeluaran->count() . ' pengajuan, ' . $pengeluaran->sum('jumlah_ternak') . ' ekor'];
        $summary[] = ['Pemasukan', $pemasukan->count() . ' pengajuan, ' . $pemasukan->sum('jumlah_ternak') . ' ekor'];
        $summary[] = [];
        
        // Breakdown Status
        $summary[] = ['BREAKDOWN STATUS', ''];
        $summary[] = ['Disetujui', $this->data->where('status', 'disetujui')->count()];
        $summary[] = ['Ditolak', $this->data->where('status', 'ditolak')->count()];
        $summary[] = ['Menunggu', $this->data->where('status', 'menunggu')->count()];
        $summary[] = ['Diproses', $this->data->where('status', 'diproses')->count()];
        $summary[] = ['Selesai', $this->data->where('status', 'selesai')->count()];
        $summary[] = [];
        
        // Breakdown Jenis Kelamin
        $summary[] = ['BREAKDOWN JENIS KELAMIN', ''];
        $jantan = $this->data->where('jenis_kelamin', 'jantan');
        $betina = $this->data->where('jenis_kelamin', 'betina');
        $summary[] = ['Jantan', $jantan->sum('jumlah_ternak') . ' ekor'];
        $summary[] = ['Betina', $betina->sum('jumlah_ternak') . ' ekor'];
        $summary[] = [];
        
        // Top 5 Kab/Kota Asal
        $summary[] = ['TOP 5 KAB/KOTA ASAL', ''];
        $topAsal = $this->data->groupBy('kab_kota_asal_id')
            ->map(function ($items) {
                return $items->sum('jumlah_ternak');
            })
            ->sortDesc()
            ->take(5);
        
        $index = 1;
        foreach ($topAsal as $kabKotaId => $total) {
            $namaKabKota = $kabKotaMap->get($kabKotaId, '-');
            $summary[] = [$index . '. ' . $namaKabKota, $total . ' ekor'];
            $index++;
        }
        $summary[] = [];
        
        // Top 5 Kab/Kota Tujuan
        $summary[] = ['TOP 5 KAB/KOTA TUJUAN', ''];
        $topTujuan = $this->data->groupBy('kab_kota_tujuan_id')
            ->map(function ($items) {
                return $items->sum('jumlah_ternak');
            })
            ->sortDesc()
            ->take(5);
        
        $index = 1;
        foreach ($topTujuan as $kabKotaId => $total) {
            $namaKabKota = $kabKotaMap->get($kabKotaId, '-');
            $summary[] = [$index . '. ' . $namaKabKota, $total . ' ekor'];
            $index++;
        }
        
        return collect($summary);
    }


    public function title(): string
    {
        return 'Ringkasan Eksekutif';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 30,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Style header sections
                foreach ($sheet->getRowIterator() as $row) {
                    $cellValue = $sheet->getCell('A' . $row->getRowIndex())->getValue();
                    if ($cellValue && (str_contains($cellValue, 'BREAKDOWN') || str_contains($cellValue, 'TOP'))) {
                        $sheet->getStyle('A' . $row->getRowIndex() . ':B' . $row->getRowIndex())
                            ->applyFromArray([
                                'font' => ['bold' => true, 'size' => 11],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'D9E1F2'],
                                ],
                            ]);
                    }
                }
            },
        ];
    }
}

// Sheet 2: Detail Transaksi
class DetailTransactionSheet implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($pengajuan, $index) {
            return [
                'No' => $index + 1,
                'Tanggal Pengajuan' => $pengajuan->tanggal_surat_permohonan ? $pengajuan->tanggal_surat_permohonan->format('d/m/Y') : '-',
                'Nomor Surat' => $pengajuan->nomor_surat_permohonan ?? '-',
                'Tanggal Surat' => $pengajuan->tanggal_surat_permohonan ? $pengajuan->tanggal_surat_permohonan->format('d/m/Y') : '-',
                'Perusahaan' => $pengajuan->user?->nama_perusahaan ?? $pengajuan->user?->name ?? '-',
                'Nama Pemohon' => $pengajuan->user?->name ?? '-',
                'Jenis Pengajuan' => match($pengajuan->jenis_pengajuan) {
                    'antar_kab_kota' => 'Antar Kab/Kota',
                    'pengeluaran' => 'Pengeluaran',
                    'pemasukan' => 'Pemasukan',
                    default => $pengajuan->jenis_pengajuan
                },
                'Kategori Ternak' => $pengajuan->jenisTernak?->kategoriTernak?->nama ?? '-',
                'Jenis Ternak' => $pengajuan->jenisTernak?->nama ?? '-',
                'Jumlah Ternak' => $pengajuan->jumlah_ternak,
                'Jenis Kelamin' => ucfirst($pengajuan->jenis_kelamin),
                'Ras Ternak' => $pengajuan->ras_ternak ?? '-',
                'Provinsi Asal' => $pengajuan->provinsiAsal?->nama ?? '-',
                'Kab/Kota Asal' => $pengajuan->kabKotaAsal?->nama ?? $pengajuan->kab_kota_asal ?? '-',
                'Pelabuhan Asal' => $pengajuan->pelabuhan_asal ?? '-',
                'Provinsi Tujuan' => $pengajuan->provinsiTujuan?->nama ?? '-',
                'Kab/Kota Tujuan' => $pengajuan->kabKotaTujuan?->nama ?? $pengajuan->kab_kota_tujuan ?? '-',
                'Pelabuhan Tujuan' => $pengajuan->pelabuhan_tujuan ?? '-',
                'Status' => ucfirst($pengajuan->status),
                'Tahun' => $pengajuan->tahun_pengajuan,
                'Keterangan' => $pengajuan->keterangan ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Pengajuan',
            'Nomor Surat',
            'Tanggal Surat',
            'Perusahaan',
            'Nama Pemohon',
            'Jenis Pengajuan',
            'Kategori Ternak',
            'Jenis Ternak',
            'Jumlah Ternak',
            'Jenis Kelamin',
            'Ras Ternak',
            'Provinsi Asal',
            'Kab/Kota Asal',
            'Pelabuhan Asal',
            'Provinsi Tujuan',
            'Kab/Kota Tujuan',
            'Pelabuhan Tujuan',
            'Status',
            'Tahun',
            'Keterangan',
        ];
    }

    public function title(): string
    {
        return 'Detail Transaksi';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 20,
            'D' => 15,
            'E' => 25,
            'F' => 20,
            'G' => 18,
            'H' => 20,
            'I' => 20,
            'J' => 12,
            'K' => 15,
            'L' => 15,
            'M' => 20,
            'N' => 20,
            'O' => 15,
            'P' => 20,
            'Q' => 20,
            'R' => 15,
            'S' => 12,
            'T' => 8,
            'U' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Style header
                $sheet->getStyle('A1:U1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                ]);
            },
        ];
    }
}

// Sheet 3: Antar Kab/Kota
class AntarKabKotaSheet implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data->where('jenis_pengajuan', 'antar_kab_kota');
    }

    public function collection()
    {
        // Pre-load semua KabKota yang dibutuhkan
        $kabKotaIds = $this->data->pluck('kab_kota_asal_id')
            ->merge($this->data->pluck('kab_kota_tujuan_id'))
            ->filter()
            ->unique();
        
        $kabKotaMap = KabKota::whereIn('id', $kabKotaIds)->pluck('nama', 'id');
        
        $result = [];
        $index = 1;
        
        foreach ($this->data as $pengajuan) {
            $result[] = [
                'No' => $index++,
                'Tanggal' => $pengajuan->tanggal_surat_permohonan ? $pengajuan->tanggal_surat_permohonan->format('d/m/Y') : '-',
                'Kab/Kota Asal' => $pengajuan->kabKotaAsal?->nama ?? $pengajuan->kab_kota_asal ?? '-',
                'Kab/Kota Tujuan' => $pengajuan->kabKotaTujuan?->nama ?? $pengajuan->kab_kota_tujuan ?? '-',
                'Jenis Ternak' => $pengajuan->jenisTernak?->nama ?? '-',
                'Jenis Kelamin' => ucfirst($pengajuan->jenis_kelamin),
                'Jumlah' => $pengajuan->jumlah_ternak,
                'Status' => ucfirst($pengajuan->status),
            ];
        }
        
        // Tambahkan summary
        if (!empty($result)) {
            $result[] = [];
            $result[] = ['SUMMARY', '', '', '', '', '', '', ''];
            
            // Total per kab/kota asal
            $summaryAsal = $this->data->groupBy('kab_kota_asal_id')
                ->map(function ($items) {
                    return $items->sum('jumlah_ternak');
                })
                ->sortDesc();
            
            $result[] = ['TOTAL PER KAB/KOTA ASAL', '', '', '', '', '', '', ''];
            foreach ($summaryAsal as $kabKotaId => $total) {
                $namaKabKota = $kabKotaMap->get($kabKotaId, '-');
                $result[] = ['', $namaKabKota, '', '', '', '', $total . ' ekor', ''];
            }
            
            $result[] = [];
            
            // Total per kab/kota tujuan
            $summaryTujuan = $this->data->groupBy('kab_kota_tujuan_id')
                ->map(function ($items) {
                    return $items->sum('jumlah_ternak');
                })
                ->sortDesc();
            
            $result[] = ['TOTAL PER KAB/KOTA TUJUAN', '', '', '', '', '', '', ''];
            foreach ($summaryTujuan as $kabKotaId => $total) {
                $namaKabKota = $kabKotaMap->get($kabKotaId, '-');
                $result[] = ['', '', $namaKabKota, '', '', '', $total . ' ekor', ''];
            }
        }
        
        return collect($result);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kab/Kota Asal',
            'Kab/Kota Tujuan',
            'Jenis Ternak',
            'Jenis Kelamin',
            'Jumlah',
            'Status',
        ];
    }

    public function title(): string
    {
        return 'Antar Kab/Kota';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 25,
            'D' => 25,
            'E' => 20,
            'F' => 15,
            'G' => 12,
            'H' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Style header
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                ]);
            },
        ];
    }
}

// Sheet 4: Pengeluaran
class PengeluaranSheet implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data->where('jenis_pengajuan', 'pengeluaran');
    }

    public function collection()
    {
        // Pre-load semua Provinsi yang dibutuhkan
        $provinsiIds = $this->data->pluck('provinsi_tujuan_id')->filter()->unique();
        $provinsiMap = Provinsi::whereIn('id', $provinsiIds)->pluck('nama', 'id');
        
        $result = [];
        $index = 1;
        
        foreach ($this->data as $pengajuan) {
            $result[] = [
                'No' => $index++,
                'Tanggal' => $pengajuan->tanggal_surat_permohonan ? $pengajuan->tanggal_surat_permohonan->format('d/m/Y') : '-',
                'Kab/Kota Asal' => $pengajuan->kabKotaAsal?->nama ?? $pengajuan->kab_kota_asal ?? '-',
                'Provinsi Tujuan' => $pengajuan->provinsiTujuan?->nama ?? '-',
                'Kab/Kota Tujuan' => $pengajuan->kab_kota_tujuan ?? '-',
                'Jenis Ternak' => $pengajuan->jenisTernak?->nama ?? '-',
                'Jenis Kelamin' => ucfirst($pengajuan->jenis_kelamin),
                'Jumlah' => $pengajuan->jumlah_ternak,
                'Status' => ucfirst($pengajuan->status),
            ];
        }
        
        // Tambahkan summary
        if (!empty($result)) {
            $result[] = [];
            $result[] = ['SUMMARY', '', '', '', '', '', '', ''];
            
            // Total per provinsi tujuan
            $summaryProvinsi = $this->data->groupBy('provinsi_tujuan_id')
                ->map(function ($items) {
                    return $items->sum('jumlah_ternak');
                })
                ->sortDesc();
            
            $result[] = ['TOTAL PER PROVINSI TUJUAN', '', '', '', '', '', '', ''];
            foreach ($summaryProvinsi as $provinsiId => $total) {
                $namaProvinsi = $provinsiMap->get($provinsiId, '-');
                $result[] = ['', '', $namaProvinsi, '', '', '', $total . ' ekor', ''];
            }
        }
        
        return collect($result);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kab/Kota Asal',
            'Provinsi Tujuan',
            'Kab/Kota Tujuan',
            'Jenis Ternak',
            'Jenis Kelamin',
            'Jumlah',
            'Status',
        ];
    }

    public function title(): string
    {
        return 'Pengeluaran';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 20,
            'G' => 15,
            'H' => 12,
            'I' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Style header
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                ]);
            },
        ];
    }
}

// Sheet 5: Pemasukan
class PemasukanSheet implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data->where('jenis_pengajuan', 'pemasukan');
    }

    public function collection()
    {
        // Pre-load semua Provinsi yang dibutuhkan
        $provinsiIds = $this->data->pluck('provinsi_asal_id')->filter()->unique();
        $provinsiMap = Provinsi::whereIn('id', $provinsiIds)->pluck('nama', 'id');
        
        $result = [];
        $index = 1;
        
        foreach ($this->data as $pengajuan) {
            $result[] = [
                'No' => $index++,
                'Tanggal' => $pengajuan->tanggal_surat_permohonan ? $pengajuan->tanggal_surat_permohonan->format('d/m/Y') : '-',
                'Provinsi Asal' => $pengajuan->provinsiAsal?->nama ?? '-',
                'Kab/Kota Asal' => $pengajuan->kab_kota_asal ?? '-',
                'Kab/Kota Tujuan' => $pengajuan->kabKotaTujuan?->nama ?? $pengajuan->kab_kota_tujuan ?? '-',
                'Jenis Ternak' => $pengajuan->jenisTernak?->nama ?? '-',
                'Jenis Kelamin' => ucfirst($pengajuan->jenis_kelamin),
                'Jumlah' => $pengajuan->jumlah_ternak,
                'Status' => ucfirst($pengajuan->status),
            ];
        }
        
        // Tambahkan summary
        if (!empty($result)) {
            $result[] = [];
            $result[] = ['SUMMARY', '', '', '', '', '', '', ''];
            
            // Total per provinsi asal
            $summaryProvinsi = $this->data->groupBy('provinsi_asal_id')
                ->map(function ($items) {
                    return $items->sum('jumlah_ternak');
                })
                ->sortDesc();
            
            $result[] = ['TOTAL PER PROVINSI ASAL', '', '', '', '', '', '', ''];
            foreach ($summaryProvinsi as $provinsiId => $total) {
                $namaProvinsi = $provinsiMap->get($provinsiId, '-');
                $result[] = ['', $namaProvinsi, '', '', '', '', $total . ' ekor', ''];
            }
        }
        
        return collect($result);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Provinsi Asal',
            'Kab/Kota Asal',
            'Kab/Kota Tujuan',
            'Jenis Ternak',
            'Jenis Kelamin',
            'Jumlah',
            'Status',
        ];
    }

    public function title(): string
    {
        return 'Pemasukan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 25,
            'D' => 25,
            'E' => 25,
            'F' => 20,
            'G' => 15,
            'H' => 12,
            'I' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Style header
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                ]);
            },
        ];
    }
}

// Sheet 6: Analisis Kuota
class AnalisisKuotaSheet implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Ambil data kuota dan penggunaan dari data pengajuan yang disetujui
        $pengajuanDisetujui = $this->data->where('status', 'disetujui');
        
        // Pre-load semua KabKota yang dibutuhkan
        $kabKotaIds = $pengajuanDisetujui->pluck('kab_kota_asal_id')
            ->merge($pengajuanDisetujui->pluck('kab_kota_tujuan_id'))
            ->filter()
            ->unique();
        
        $kabKotaMap = KabKota::whereIn('id', $kabKotaIds)->pluck('nama', 'id');
        
        $result = [];
        $result[] = ['Jenis Ternak', 'Kab/Kota', 'Jenis Kelamin', 'Jenis Kuota', 'Jumlah Terpakai', 'Keterangan'];
        
        // Group by jenis ternak, kab/kota, jenis kelamin, jenis kuota
        $grouped = $pengajuanDisetujui->groupBy(function ($item) {
            return $item->jenis_ternak_id . '_' . 
                   ($item->kab_kota_asal_id ?? $item->kab_kota_tujuan_id ?? 'null') . '_' . 
                   $item->jenis_kelamin . '_' . 
                   ($item->jenis_pengajuan === 'pengeluaran' ? 'pengeluaran' : 
                    ($item->jenis_pengajuan === 'pemasukan' ? 'pemasukan' : 'pengeluaran'));
        });
        
        foreach ($grouped as $group) {
            $first = $group->first();
            $total = $group->sum('jumlah_ternak');
            $kabKotaId = $first->kab_kota_asal_id ?? $first->kab_kota_tujuan_id;
            $namaKabKota = $kabKotaId ? ($kabKotaMap->get($kabKotaId) ?? '-') : '-';
            $jenisKuota = $first->jenis_pengajuan === 'pengeluaran' ? 'Pengeluaran' : 
                         ($first->jenis_pengajuan === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran');
            
            $result[] = [
                $first->jenisTernak?->nama ?? '-',
                $namaKabKota,
                ucfirst($first->jenis_kelamin),
                $jenisKuota,
                $total . ' ekor',
                $total > 0 ? 'Terpakai' : '-',
            ];
        }
        
        return collect($result);
    }

    public function headings(): array
    {
        return [
            'Jenis Ternak',
            'Kab/Kota',
            'Jenis Kelamin',
            'Jenis Kuota',
            'Jumlah Terpakai',
            'Keterangan',
        ];
    }

    public function title(): string
    {
        return 'Analisis Kuota';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Style header
                $sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                ]);
            },
        ];
    }
}

// Sheet 7: Breakdown Perusahaan
class BreakdownPerusahaanSheet implements FromCollection, WithHeadings, WithTitle, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $result = [];
        $result[] = ['Perusahaan', 'Total Pengajuan', 'Total Jumlah Ternak', 'Antar Kab/Kota', 'Pengeluaran', 'Pemasukan'];
        
        $grouped = $this->data->groupBy('user_id');
        
        foreach ($grouped as $userId => $items) {
            $user = $items->first()->user;
            $namaPerusahaan = $user?->nama_perusahaan ?? $user?->name ?? '-';
            $totalPengajuan = $items->count();
            $totalTernak = $items->sum('jumlah_ternak');
            $antarKabKota = $items->where('jenis_pengajuan', 'antar_kab_kota')->sum('jumlah_ternak');
            $pengeluaran = $items->where('jenis_pengajuan', 'pengeluaran')->sum('jumlah_ternak');
            $pemasukan = $items->where('jenis_pengajuan', 'pemasukan')->sum('jumlah_ternak');
            
            $result[] = [
                $namaPerusahaan,
                $totalPengajuan,
                $totalTernak . ' ekor',
                $antarKabKota . ' ekor',
                $pengeluaran . ' ekor',
                $pemasukan . ' ekor',
            ];
        }
        
        return collect($result);
    }

    public function headings(): array
    {
        return [
            'Perusahaan',
            'Total Pengajuan',
            'Total Jumlah Ternak',
            'Antar Kab/Kota',
            'Pengeluaran',
            'Pemasukan',
        ];
    }

    public function title(): string
    {
        return 'Breakdown Perusahaan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 18,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Style header
                $sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                ]);
            },
        ];
    }
}

