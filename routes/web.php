<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('utama');

Route::get('/daftar', function () {
    return redirect('/admin/register');
})->name('daftar');
