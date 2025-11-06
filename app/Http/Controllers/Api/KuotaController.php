<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kuota;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KuotaController extends Controller
{
    /**
     * Get all kuota data with optional filters
     * 
     * Query parameters:
     * - tahun: Filter by year
     * - jenis_kuota: Filter by 'pemasukan' or 'pengeluaran'
     * - jenis_ternak_id: Filter by jenis ternak ID
     * - kab_kota_id: Filter by kab/kota ID
     * - pulau: Filter by pulau
     * - jenis_kelamin: Filter by jenis kelamin
     */
    public function index(Request $request): JsonResponse
    {
        $query = Kuota::with(['jenisTernak', 'kabKota']);

        // Apply filters
        if ($request->has('tahun')) {
            $query->where('tahun', $request->tahun);
        } else {
            // Default to current year if not specified
            $query->where('tahun', date('Y'));
        }

        if ($request->has('jenis_kuota')) {
            $query->where('jenis_kuota', $request->jenis_kuota);
        }

        if ($request->has('jenis_ternak_id')) {
            $query->where('jenis_ternak_id', $request->jenis_ternak_id);
        }

        if ($request->has('kab_kota_id')) {
            $query->where('kab_kota_id', $request->kab_kota_id);
        }

        if ($request->has('pulau')) {
            $query->where('pulau', $request->pulau);
        }

        if ($request->has('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        $kuotas = $query->get();

        $data = $kuotas->map(function ($kuota) {
            return [
                'id' => $kuota->id,
                'jenis_ternak' => [
                    'id' => $kuota->jenisTernak->id,
                    'nama' => $kuota->jenisTernak->nama,
                ],
                'wilayah' => [
                    'kab_kota_id' => $kuota->kab_kota_id,
                    'kab_kota_nama' => $kuota->kabKota?->nama,
                    'pulau' => $kuota->pulau,
                    'lokasi_display' => $kuota->lokasi_display,
                ],
                'tahun' => $kuota->tahun,
                'jenis_kuota' => $kuota->jenis_kuota,
                'jenis_kelamin' => $kuota->jenis_kelamin,
                'kuota_total' => $kuota->kuota,
                'kuota_terpakai' => $kuota->kuota_terpakai,
                'kuota_tersisa' => $kuota->kuota_sisa,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
                'tahun' => $request->input('tahun', date('Y')),
            ],
        ]);
    }

    /**
     * Get kuota pemasukan grouped by jenis ternak and wilayah
     */
    public function pemasukan(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        $query = Kuota::with(['jenisTernak', 'kabKota'])
            ->where('jenis_kuota', 'pemasukan')
            ->where('tahun', $tahun);

        // Apply optional filters
        if ($request->has('jenis_ternak_id')) {
            $query->where('jenis_ternak_id', $request->jenis_ternak_id);
        }

        if ($request->has('kab_kota_id')) {
            $query->where('kab_kota_id', $request->kab_kota_id);
        }

        if ($request->has('pulau')) {
            $query->where('pulau', $request->pulau);
        }

        if ($request->has('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        $kuotas = $query->get();

        // Group by jenis ternak and wilayah
        $grouped = $kuotas->groupBy(function ($kuota) {
            $wilayahKey = $kuota->kab_kota_id 
                ? "kab_kota_{$kuota->kab_kota_id}" 
                : "pulau_{$kuota->pulau}";
            
            return "{$kuota->jenis_ternak_id}_{$wilayahKey}_{$kuota->jenis_kelamin}";
        });

        $data = $grouped->map(function ($items, $key) {
            $firstItem = $items->first();
            
            $totalKuota = $items->sum('kuota');
            $totalTerpakai = $items->sum(function ($item) {
                return $item->kuota_terpakai;
            });
            $totalTersisa = $totalKuota - $totalTerpakai;

            return [
                'jenis_ternak' => [
                    'id' => $firstItem->jenisTernak->id,
                    'nama' => $firstItem->jenisTernak->nama,
                ],
                'wilayah' => [
                    'kab_kota_id' => $firstItem->kab_kota_id,
                    'kab_kota_nama' => $firstItem->kabKota?->nama,
                    'pulau' => $firstItem->pulau,
                    'lokasi_display' => $firstItem->lokasi_display,
                ],
                'tahun' => $firstItem->tahun,
                'jenis_kelamin' => $firstItem->jenis_kelamin,
                'kuota_total' => $totalKuota,
                'kuota_terpakai' => $totalTerpakai,
                'kuota_tersisa' => $totalTersisa,
                'detail' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'kuota_total' => $item->kuota,
                        'kuota_terpakai' => $item->kuota_terpakai,
                        'kuota_tersisa' => $item->kuota_sisa,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
                'tahun' => $tahun,
                'jenis_kuota' => 'pemasukan',
            ],
        ]);
    }

    /**
     * Get kuota pengeluaran grouped by jenis ternak and wilayah
     */
    public function pengeluaran(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        $query = Kuota::with(['jenisTernak', 'kabKota'])
            ->where('jenis_kuota', 'pengeluaran')
            ->where('tahun', $tahun);

        // Apply optional filters
        if ($request->has('jenis_ternak_id')) {
            $query->where('jenis_ternak_id', $request->jenis_ternak_id);
        }

        if ($request->has('kab_kota_id')) {
            $query->where('kab_kota_id', $request->kab_kota_id);
        }

        if ($request->has('pulau')) {
            $query->where('pulau', $request->pulau);
        }

        if ($request->has('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        $kuotas = $query->get();

        // Group by jenis ternak and wilayah
        $grouped = $kuotas->groupBy(function ($kuota) {
            $wilayahKey = $kuota->kab_kota_id 
                ? "kab_kota_{$kuota->kab_kota_id}" 
                : "pulau_{$kuota->pulau}";
            
            return "{$kuota->jenis_ternak_id}_{$wilayahKey}_{$kuota->jenis_kelamin}";
        });

        $data = $grouped->map(function ($items, $key) {
            $firstItem = $items->first();
            
            $totalKuota = $items->sum('kuota');
            $totalTerpakai = $items->sum(function ($item) {
                return $item->kuota_terpakai;
            });
            $totalTersisa = $totalKuota - $totalTerpakai;

            return [
                'jenis_ternak' => [
                    'id' => $firstItem->jenisTernak->id,
                    'nama' => $firstItem->jenisTernak->nama,
                ],
                'wilayah' => [
                    'kab_kota_id' => $firstItem->kab_kota_id,
                    'kab_kota_nama' => $firstItem->kabKota?->nama,
                    'pulau' => $firstItem->pulau,
                    'lokasi_display' => $firstItem->lokasi_display,
                ],
                'tahun' => $firstItem->tahun,
                'jenis_kelamin' => $firstItem->jenis_kelamin,
                'kuota_total' => $totalKuota,
                'kuota_terpakai' => $totalTerpakai,
                'kuota_tersisa' => $totalTersisa,
                'detail' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'kuota_total' => $item->kuota,
                        'kuota_terpakai' => $item->kuota_terpakai,
                        'kuota_tersisa' => $item->kuota_sisa,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
                'tahun' => $tahun,
                'jenis_kuota' => 'pengeluaran',
            ],
        ]);
    }
}

