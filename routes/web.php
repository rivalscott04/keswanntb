<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
})->name('utama');

Route::get('/daftar', function () {
    return redirect('/admin/register');
})->name('daftar');

Route::get('/laporan', [ReportController::class, 'index'])->name('laporan');
Route::post('/laporan/generate', [ReportController::class, 'generate'])->name('laporan.generate');
