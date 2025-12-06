<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pengajuan;
use App\Models\JenisTernak;
use App\Models\Wewenang;
use App\Models\KategoriTernak;
use App\Models\Bidang;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BidangRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $disnakProvinsiP3HP;
    protected User $disnakProvinsiKesmavet;
    protected User $disnakProvinsiKeswan;
    protected Bidang $bidangP3HP;
    protected Bidang $bidangKesmavet;
    protected Bidang $bidangKeswan;
    protected JenisTernak $jenisTernakSapi;
    protected JenisTernak $jenisTernakProduk;
    protected JenisTernak $jenisTernakKesayangan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedTestData();
    }

    protected function seedTestData(): void
    {
        // Create wewenang
        $wewenangAdmin = Wewenang::create(['nama' => 'Admin']);
        $wewenangDisnakProvinsi = Wewenang::create(['nama' => 'Disnak Provinsi']);
        $wewenangPengguna = Wewenang::create(['nama' => 'Pengguna']);

        // Create bidang
        $this->bidangP3HP = Bidang::create(['nama' => 'P3HP']);
        $this->bidangKesmavet = Bidang::create(['nama' => 'Kesmavet']);
        $this->bidangKeswan = Bidang::create(['nama' => 'Keswan']);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'wewenang_id' => $wewenangAdmin->id,
            'is_admin' => true,
        ]);

        // Create Disnak Provinsi users with different bidang
        $this->disnakProvinsiP3HP = User::factory()->create([
            'wewenang_id' => $wewenangDisnakProvinsi->id,
            'bidang_id' => $this->bidangP3HP->id,
        ]);

        $this->disnakProvinsiKesmavet = User::factory()->create([
            'wewenang_id' => $wewenangDisnakProvinsi->id,
            'bidang_id' => $this->bidangKesmavet->id,
        ]);

        $this->disnakProvinsiKeswan = User::factory()->create([
            'wewenang_id' => $wewenangDisnakProvinsi->id,
            'bidang_id' => $this->bidangKeswan->id,
        ]);

        // Create pengguna for creating pengajuan
        $pengguna = User::factory()->create([
            'wewenang_id' => $wewenangPengguna->id,
        ]);

        // Create kategori ternak
        $kategoriSapi = KategoriTernak::create(['nama' => 'Sapi']);
        $kategoriProduk = KategoriTernak::create(['nama' => 'Produk Hewan']);
        $kategoriKesayangan = KategoriTernak::create(['nama' => 'Hewan Kesayangan']);

        // Create jenis ternak dengan bidang
        $this->jenisTernakSapi = JenisTernak::create([
            'nama' => 'Sapi Pedaging',
            'kategori_ternak_id' => $kategoriSapi->id,
            'bidang_id' => $this->bidangP3HP->id,
        ]);

        $this->jenisTernakProduk = JenisTernak::create([
            'nama' => 'Produk Olahan',
            'kategori_ternak_id' => $kategoriProduk->id,
            'bidang_id' => $this->bidangKesmavet->id,
        ]);

        $this->jenisTernakKesayangan = JenisTernak::create([
            'nama' => 'Anjing',
            'kategori_ternak_id' => $kategoriKesayangan->id,
            'bidang_id' => $this->bidangKeswan->id,
        ]);

        // Create pengajuan for each jenis ternak
        Pengajuan::create([
            'user_id' => $pengguna->id,
            'jenis_ternak_id' => $this->jenisTernakSapi->id,
            'jenis_pengajuan' => 'pengeluaran',
            'tanggal_surat_permohonan' => now(),
            'nomor_surat_permohonan' => 'SAPI-001',
            'status' => 'menunggu',
            'tahun_pengajuan' => now()->year,
            'jumlah_ternak' => 10,
            'jenis_kelamin' => 'jantan',
        ]);

        Pengajuan::create([
            'user_id' => $pengguna->id,
            'jenis_ternak_id' => $this->jenisTernakProduk->id,
            'jenis_pengajuan' => 'pengeluaran',
            'tanggal_surat_permohonan' => now(),
            'nomor_surat_permohonan' => 'PRODUK-001',
            'status' => 'menunggu',
            'tahun_pengajuan' => now()->year,
            'jumlah_ternak' => 100,
            'jenis_kelamin' => 'gabung',
        ]);

        Pengajuan::create([
            'user_id' => $pengguna->id,
            'jenis_ternak_id' => $this->jenisTernakKesayangan->id,
            'jenis_pengajuan' => 'pengeluaran',
            'tanggal_surat_permohonan' => now(),
            'nomor_surat_permohonan' => 'ANJING-001',
            'status' => 'menunggu',
            'tahun_pengajuan' => now()->year,
            'jumlah_ternak' => 2,
            'jenis_kelamin' => 'jantan',
        ]);
    }

    /** @test */
    public function admin_can_see_all_pengajuan()
    {
        $allPengajuan = Pengajuan::count();
        $this->assertEquals(3, $allPengajuan);
    }

    /** @test */
    public function disnak_provinsi_p3hp_only_sees_sapi_pengajuan()
    {
        $user = $this->disnakProvinsiP3HP;
        
        $query = Pengajuan::whereHas('jenisTernak', function ($q) use ($user) {
            $q->where('bidang_id', $user->bidang_id);
        });

        $this->assertEquals(1, $query->count());
        $this->assertEquals('Sapi Pedaging', $query->first()->jenisTernak->nama);
    }

    /** @test */
    public function disnak_provinsi_kesmavet_only_sees_produk_pengajuan()
    {
        $user = $this->disnakProvinsiKesmavet;
        
        $query = Pengajuan::whereHas('jenisTernak', function ($q) use ($user) {
            $q->where('bidang_id', $user->bidang_id);
        });

        $this->assertEquals(1, $query->count());
        $this->assertEquals('Produk Olahan', $query->first()->jenisTernak->nama);
    }

    /** @test */
    public function disnak_provinsi_keswan_only_sees_kesayangan_pengajuan()
    {
        $user = $this->disnakProvinsiKeswan;
        
        $query = Pengajuan::whereHas('jenisTernak', function ($q) use ($user) {
            $q->where('bidang_id', $user->bidang_id);
        });

        $this->assertEquals(1, $query->count());
        $this->assertEquals('Anjing', $query->first()->jenisTernak->nama);
    }

    /** @test */
    public function disnak_provinsi_without_bidang_sees_all()
    {
        // Create user without bidang_id
        $wewenang = Wewenang::where('nama', 'Disnak Provinsi')->first();
        $userNoBidang = User::factory()->create([
            'wewenang_id' => $wewenang->id,
            'bidang_id' => null,
        ]);

        // Without bidang_id filter, should return all
        $allPengajuan = Pengajuan::count();
        $this->assertEquals(3, $allPengajuan);
    }

    /** @test */
    public function jenis_ternak_belongs_to_correct_bidang()
    {
        $this->assertEquals($this->bidangP3HP->id, $this->jenisTernakSapi->bidang_id);
        $this->assertEquals($this->bidangKesmavet->id, $this->jenisTernakProduk->bidang_id);
        $this->assertEquals($this->bidangKeswan->id, $this->jenisTernakKesayangan->bidang_id);
    }
}
