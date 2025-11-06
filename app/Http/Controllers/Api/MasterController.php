<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisTernak;
use App\Models\KabKota;
use App\Models\Provinsi;
use App\Models\KategoriTernak;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MasterController extends Controller
{
    /**
     * Get all jenis ternak
     * 
     * Query parameters:
     * - kategori_ternak_id: Filter by kategori ternak ID
     * - bidang_id: Filter by bidang ID
     */
    public function jenisTernak(Request $request): JsonResponse
    {
        $query = JenisTernak::with(['kategoriTernak', 'bidang']);

        if ($request->has('kategori_ternak_id')) {
            $query->where('kategori_ternak_id', $request->kategori_ternak_id);
        }

        if ($request->has('bidang_id')) {
            $query->where('bidang_id', $request->bidang_id);
        }

        $jenisTernaks = $query->orderBy('nama')->get();

        $data = $jenisTernaks->map(function ($jenisTernak) {
            return [
                'id' => $jenisTernak->id,
                'nama' => $jenisTernak->nama,
                'kategori_ternak' => [
                    'id' => $jenisTernak->kategoriTernak->id,
                    'nama' => $jenisTernak->kategoriTernak->nama,
                ],
                'bidang' => [
                    'id' => $jenisTernak->bidang->id,
                    'nama' => $jenisTernak->bidang->nama,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
            ],
        ]);
    }

    /**
     * Get jenis ternak by ID
     */
    public function jenisTernakById($id): JsonResponse
    {
        $jenisTernak = JenisTernak::with(['kategoriTernak', 'bidang'])->find($id);

        if (!$jenisTernak) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis ternak tidak ditemukan',
            ], 404);
        }

        $data = [
            'id' => $jenisTernak->id,
            'nama' => $jenisTernak->nama,
            'kategori_ternak' => [
                'id' => $jenisTernak->kategoriTernak->id,
                'nama' => $jenisTernak->kategoriTernak->nama,
            ],
            'bidang' => [
                'id' => $jenisTernak->bidang->id,
                'nama' => $jenisTernak->bidang->nama,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get all kabupaten/kota
     * 
     * Query parameters:
     * - provinsi_id: Filter by provinsi ID
     */
    public function kabKota(Request $request): JsonResponse
    {
        $query = KabKota::with('provinsi');

        if ($request->has('provinsi_id')) {
            $query->where('provinsi_id', $request->provinsi_id);
        }

        $kabKotas = $query->orderBy('nama')->get();

        $data = $kabKotas->map(function ($kabKota) {
            return [
                'id' => $kabKota->id,
                'nama' => $kabKota->nama,
                'provinsi' => [
                    'id' => $kabKota->provinsi->id,
                    'nama' => $kabKota->provinsi->nama,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
            ],
        ]);
    }

    /**
     * Get kabupaten/kota by ID
     */
    public function kabKotaById($id): JsonResponse
    {
        $kabKota = KabKota::with('provinsi')->find($id);

        if (!$kabKota) {
            return response()->json([
                'success' => false,
                'message' => 'Kabupaten/Kota tidak ditemukan',
            ], 404);
        }

        $data = [
            'id' => $kabKota->id,
            'nama' => $kabKota->nama,
            'provinsi' => [
                'id' => $kabKota->provinsi->id,
                'nama' => $kabKota->provinsi->nama,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get all provinsi
     */
    public function provinsi(): JsonResponse
    {
        $provinsis = Provinsi::orderBy('nama')->get();

        $data = $provinsis->map(function ($provinsi) {
            return [
                'id' => $provinsi->id,
                'nama' => $provinsi->nama,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
            ],
        ]);
    }

    /**
     * Get provinsi by ID
     */
    public function provinsiById($id): JsonResponse
    {
        $provinsi = Provinsi::with('kabKotas')->find($id);

        if (!$provinsi) {
            return response()->json([
                'success' => false,
                'message' => 'Provinsi tidak ditemukan',
            ], 404);
        }

        $data = [
            'id' => $provinsi->id,
            'nama' => $provinsi->nama,
            'kab_kota' => $provinsi->kabKotas->map(function ($kabKota) {
                return [
                    'id' => $kabKota->id,
                    'nama' => $kabKota->nama,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get all kategori ternak
     */
    public function kategoriTernak(): JsonResponse
    {
        $kategoriTernaks = KategoriTernak::orderBy('nama')->get();

        $data = $kategoriTernaks->map(function ($kategoriTernak) {
            return [
                'id' => $kategoriTernak->id,
                'nama' => $kategoriTernak->nama,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
            ],
        ]);
    }

    /**
     * Get kategori ternak by ID
     */
    public function kategoriTernakById($id): JsonResponse
    {
        $kategoriTernak = KategoriTernak::with('jenisTernak')->find($id);

        if (!$kategoriTernak) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori ternak tidak ditemukan',
            ], 404);
        }

        $data = [
            'id' => $kategoriTernak->id,
            'nama' => $kategoriTernak->nama,
            'jenis_ternak' => $kategoriTernak->jenisTernak->map(function ($jenisTernak) {
                return [
                    'id' => $jenisTernak->id,
                    'nama' => $jenisTernak->nama,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}

