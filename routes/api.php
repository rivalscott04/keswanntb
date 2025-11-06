<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KuotaController;
use App\Http\Controllers\Api\MasterController;

Route::prefix('master')->group(function () {
    // Jenis Ternak
    Route::get('/jenis-ternak', [MasterController::class, 'jenisTernak']);
    Route::get('/jenis-ternak/{id}', [MasterController::class, 'jenisTernakById']);
    
    // Kabupaten/Kota
    Route::get('/kab-kota', [MasterController::class, 'kabKota']);
    Route::get('/kab-kota/{id}', [MasterController::class, 'kabKotaById']);
    
    // Provinsi
    Route::get('/provinsi', [MasterController::class, 'provinsi']);
    Route::get('/provinsi/{id}', [MasterController::class, 'provinsiById']);
    
    // Kategori Ternak
    Route::get('/kategori-ternak', [MasterController::class, 'kategoriTernak']);
    Route::get('/kategori-ternak/{id}', [MasterController::class, 'kategoriTernakById']);
});

Route::prefix('kuota')->group(function () {
    Route::get('/', [KuotaController::class, 'index']);
    Route::get('/pemasukan', [KuotaController::class, 'pemasukan']);
    Route::get('/pengeluaran', [KuotaController::class, 'pengeluaran']);
});

