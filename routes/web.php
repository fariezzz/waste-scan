<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EdukasiController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AIController;

Route::get('/', function () {
    return view('index',
    ['title' => 'WASTE — Landing Page']);
});

Route::get('/scan', function () {
    return view('scan.index',
    ['title' => 'WASTE — Scan']);
});

Route::get('/edukasi', [EdukasiController::class, 'index'])->name('edukasi.index');

Route::get('/edukasi/{slug}', [EdukasiController::class, 'show'])->name('edukasi.show');

Route::get('/riwayat', function () {
    return view('riwayat.index',
    ['title' => 'WASTE — Riwayat Scan']);
})->name('riwayat');

Route::get('/chatbot', [ChatbotController::class, 'index']);
Route::post('/chatbot', [ChatbotController::class, 'chat']);

Route::post('/ai/process', [AIController::class, 'process']);
