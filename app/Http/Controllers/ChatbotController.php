<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function index(){
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

        if ($response->failed()) {
            return response()->json([
                "reply" => "⚠️ Chatbot sedang tidak tersedia.",
            ]);
        }

        return $response->json();
    }
}
