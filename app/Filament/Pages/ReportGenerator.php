<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Services\ReportService;
use App\Models\JenisTernak;
use Illuminate\Support\Facades\Log;

class ReportGenerator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $navigationGroup = null;
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.report-generator';

    protected static ?string $title = 'Generator Laporan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_mulai' => now()->startOfMonth(),
            'tanggal_akhir' => now()->endOfMonth(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('Filter Laporan')
                    ->description('Pilih rentang tanggal dan jenis ternak untuk generate laporan')
                    ->schema([
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()->startOfMonth())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->helperText('Pilih tanggal mulai periode laporan'),
                        
                        DatePicker::make('tanggal_akhir')
                            ->label('Tanggal Akhir')
                            ->required()
                            ->default(now()->endOfMonth())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->helperText('Pilih tanggal akhir periode laporan')
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $tanggalMulai = $get('tanggal_mulai');
                                if ($tanggalMulai && $state && $state < $tanggalMulai) {
                                    Notification::make()
                                        ->warning()
                                        ->title('Peringatan')
                                        ->body('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai')
                                        ->send();
                                    $set('tanggal_akhir', $tanggalMulai);
                                }
                            }),
                        
                        Select::make('jenis_ternak_ids')
                            ->label('Jenis Ternak')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                return JenisTernak::with('kategoriTernak')
                                    ->get()
                                    ->groupBy('kategoriTernak.nama')
                                    ->map(function ($jenisTernakGroup) {
                                        return $jenisTernakGroup->pluck('nama', 'id');
                                    })
                                    ->toArray();
                            })
                            ->helperText('Pilih jenis ternak (kosongkan untuk semua jenis ternak)')
                            ->placeholder('Pilih jenis ternak...'),
                        
                        Select::make('jenis_pengajuan')
                            ->label('Jenis Pengajuan')
                            ->options([
                                '' => 'Semua Jenis Pengajuan',
                                'pengeluaran' => 'Pengeluaran',
                                'pemasukan' => 'Pemasukan',
                                'antar_kab_kota' => 'Antar Kab/Kota',
                            ])
                            ->default('')
                            ->helperText('Filter laporan berdasarkan jenis pengajuan'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function generateReport()
    {
        $data = $this->form->getState();
        
        $tanggalMulai = $data['tanggal_mulai'];
        $tanggalAkhir = $data['tanggal_akhir'];
        $jenisTernakIds = $data['jenis_ternak_ids'] ?? [];
        $jenisPengajuan = $data['jenis_pengajuan'] ?? null;

        // Validasi tanggal
        if ($tanggalAkhir < $tanggalMulai) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Tanggal akhir tidak boleh lebih kecil dari tanggal mulai')
                ->send();
            return;
        }

        try {
            Notification::make()
                ->info()
                ->title('Memproses Laporan')
                ->body('Sedang memproses laporan, mohon tunggu...')
                ->send();

            return ReportService::generateReport($tanggalMulai, $tanggalAkhir, $jenisTernakIds, $jenisPengajuan);
        } catch (\Exception $e) {
            Log::error('Error generating report: ' . $e->getMessage());
            
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Terjadi kesalahan saat generate laporan: ' . $e->getMessage())
                ->send();
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user !== null;
    }
}

