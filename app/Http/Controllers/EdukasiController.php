<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EdukasiController extends Controller
{
    public function index()
    {
        $modules = $this->loadModules();

        return view('edukasi.index', compact('modules'), [
            'title' => 'WASTE — Edukasi'
        ]);
    }

    public function show($slug)
    {
        $modules = $this->loadModules();

        $module = collect($modules)->firstWhere('slug', $slug);

        if (!$module) abort(404);

        return view('edukasi.detail', [
            'module' => $module,
            'contentView' => $module['view']
        ]);
    }

    private function loadModules()
    {
        $path = public_path('data/edukasi.json');
        $json = file_get_contents($path);

        return json_decode($json, true);
    }
}
