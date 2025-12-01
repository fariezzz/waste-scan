<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class RiwayatController extends Controller
{
    public function index()
    {
        $path = public_path('data/riwayat.json');

        if (!File::exists($path)) {
            $riwayat = [];
        } else {
            $json = File::get($path);
            $riwayat = json_decode($json, true);
        }

        return view('riwayat.index', compact('riwayat'), [
            'title' => 'WASTE — Riwayat Scan'
        ]);
    }
}