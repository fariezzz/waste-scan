<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    public function process(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
            'mode'  => 'required|in:classify,detect',
        ]);

        $file = $request->file('image');
        $mode = $request->mode;

        $fastApiUrl = $mode === 'classify'
            ? env('FASTAPI_URL_CLASSIFY', 'http://127.0.0.1:8002/classify')
            : env('FASTAPI_URL_DETECT', 'http://127.0.0.1:8002/detect');

        try {
            $response = Http::attach(
                'image',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post($fastApiUrl);

            if (!$response->successful()) {
                return response()->json([
                    'error'  => 'FastAPI error',
                    'detail' => $response->body(),
                ], 500);
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'error'     => 'Gagal menghubungi FastAPI',
                'exception' => $e->getMessage(),
            ], 500);
        }
    }
}
