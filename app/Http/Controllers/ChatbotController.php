<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot.index');
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');

        $response = Http::post(env('CHATBOT_API_URL'), [
            "messages" => [
                ["role" => "user", "content" => $message]
            ]
        ]);

        // Print isi lengkap respons untuk debugging
        return response()->json([
            "status" => $response->status(),
            "body"   => $response->body(),      // respons mentah dari Render
            "json"   => $response->json(),      // respon setelah decode
            "url"    => env('CHATBOT_API_URL'),
        ]);
    }
}
