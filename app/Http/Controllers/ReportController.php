<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use App\Models\JenisTernak;

class ReportController extends Controller
{
    public function index()
    {
        $jenisTernak = JenisTernak::with('kategoriTernak')
            ->get()
            ->groupBy('kategoriTernak.nama')
            ->map(function ($jenisTernakGroup) {
                return $jenisTernakGroup->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                    ];
                });
            });

        return view('report.index', compact('jenisTernak'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_ternak_ids' => 'nullable|array',
            'jenis_ternak_ids.*' => 'exists:jenis_ternak,id',
        ]);

        $tanggalMulai = $request->tanggal_mulai;
        $tanggalAkhir = $request->tanggal_akhir;
        $jenisTernakIds = $request->jenis_ternak_ids ?? [];

        try {
            $response = ReportService::generateReport($tanggalMulai, $tanggalAkhir, $jenisTernakIds);
            $response->headers->setCookie(cookie('download_started', 'true', 5, null, null, false, false));
            return $response;
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat generate laporan: ' . $e->getMessage()]);
        }
    }
}









