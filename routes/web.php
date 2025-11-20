<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('scan.index');
});

Route::get('/edukasi', function () {
    return view('edukasi.index');
});

Route::get('/riwayat', function () {
    return view('riwayat.index');
});

Route::post('/chatbot', function (Request $request) {
    $msg = strtolower($request->message);

    if (strpos($msg, 'halo') !== false) {
        return response()->json(['reply' => 'Halo! Ada yang bisa aku bantu?']);
    }

    if (strpos($msg, 'scan') !== false) {
        return response()->json(['reply' => 'Untuk scan sampah, arahkan kamera ke objek dan klik Capture.']);
    }

    return response()->json(['reply' => 'Maaf, aku belum mengerti. Coba ulangi pesanmu!']);
});
