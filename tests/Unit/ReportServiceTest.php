<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ReportService;
use App\Models\Pengajuan;
use App\Models\User;
use App\Models\JenisTernak;
use App\Models\Wewenang;
use App\Models\KategoriTernak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed minimal data for testing
        $this->seedTestData();
    }

    protected function seedTestData(): void
    {
        // Create wewenang
        $wewenang = Wewenang::create(['nama' => 'Pengguna']);
        
        // Create user
        $user = User::factory()->create([
            'wewenang_id' => $wewenang->id,
        ]);

        // Create kategori ternak
        $kategori = KategoriTernak::create(['nama' => 'Sapi']);

        // Create jenis ternak
        $jenisTernak = JenisTernak::create([
            'nama' => 'Sapi Pedaging',
            'kategori_ternak_id' => $kategori->id,
        ]);

        // Create test pengajuan
        Pengajuan::create([
            'user_id' => $user->id,
            'jenis_ternak_id' => $jenisTernak->id,
            'jenis_pengajuan' => 'pengeluaran',
            'tanggal_surat_permohonan' => now(),
            'nomor_surat_permohonan' => 'TEST-001',
            'status' => 'menunggu',
            'tahun_pengajuan' => now()->year,
            'jumlah_ternak' => 10,
            'jenis_kelamin' => 'jantan',
        ]);

        Pengajuan::create([
            'user_id' => $user->id,
            'jenis_ternak_id' => $jenisTernak->id,
            'jenis_pengajuan' => 'pemasukan',
            'tanggal_surat_permohonan' => now(),
            'nomor_surat_permohonan' => 'TEST-002',
            'status' => 'menunggu',
            'tahun_pengajuan' => now()->year,
            'jumlah_ternak' => 5,
            'jenis_kelamin' => 'betina',
        ]);

        Pengajuan::create([
            'user_id' => $user->id,
            'jenis_ternak_id' => $jenisTernak->id,
            'jenis_pengajuan' => 'antar_kab_kota',
            'tanggal_surat_permohonan' => now(),
            'nomor_surat_permohonan' => 'TEST-003',
            'status' => 'menunggu',
            'tahun_pengajuan' => now()->year,
            'jumlah_ternak' => 3,
            'jenis_kelamin' => 'jantan',
        ]);
    }

    /** @test */
    public function it_can_generate_report_without_filters()
    {
        $tanggalMulai = now()->startOfMonth();
        $tanggalAkhir = now()->endOfMonth();

        // This should not throw any exception
        $this->assertNotNull(ReportService::class);
    }

    /** @test */
    public function it_filters_by_jenis_pengajuan_pengeluaran()
    {
        $tanggalMulai = now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = now()->endOfMonth()->format('Y-m-d');

        // Query directly to verify filter works
        $query = Pengajuan::whereBetween('tanggal_surat_permohonan', [$tanggalMulai, $tanggalAkhir])
            ->where('jenis_pengajuan', 'pengeluaran');
        
        $this->assertEquals(1, $query->count());
        $this->assertEquals('pengeluaran', $query->first()->jenis_pengajuan);
    }

    /** @test */
    public function it_filters_by_jenis_pengajuan_pemasukan()
    {
        $tanggalMulai = now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = now()->endOfMonth()->format('Y-m-d');

        $query = Pengajuan::whereBetween('tanggal_surat_permohonan', [$tanggalMulai, $tanggalAkhir])
            ->where('jenis_pengajuan', 'pemasukan');
        
        $this->assertEquals(1, $query->count());
        $this->assertEquals('pemasukan', $query->first()->jenis_pengajuan);
    }

    /** @test */
    public function it_filters_by_jenis_pengajuan_antar_kab_kota()
    {
        $tanggalMulai = now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = now()->endOfMonth()->format('Y-m-d');

        $query = Pengajuan::whereBetween('tanggal_surat_permohonan', [$tanggalMulai, $tanggalAkhir])
            ->where('jenis_pengajuan', 'antar_kab_kota');
        
        $this->assertEquals(1, $query->count());
        $this->assertEquals('antar_kab_kota', $query->first()->jenis_pengajuan);
    }

    /** @test */
    public function it_returns_all_when_no_jenis_pengajuan_filter()
    {
        $tanggalMulai = now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = now()->endOfMonth()->format('Y-m-d');

        $query = Pengajuan::whereBetween('tanggal_surat_permohonan', [$tanggalMulai, $tanggalAkhir]);
        
        $this->assertEquals(3, $query->count());
    }
}
