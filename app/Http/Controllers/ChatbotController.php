<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot.index', [
            'title' => 'WASTE — Chatbot'
        ]);
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');
        $history = $request->input('history', []);

        $messages = [];

        if (is_array($history) && count($history) > 0) {
            foreach ($history as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $role = $item['role'] ?? null;
                $content = $item['content'] ?? null;

                if (!in_array($role, ['user', 'assistant'], true)) {
                    continue;
                }

                if (!is_string($content) || trim($content) === '') {
                    continue;
                }

                $messages[] = [
                    'role' => $role,
                    'content' => $content,
                ];
            }
        }

        if (count($messages) === 0) {
            $messages = [
                ['role' => 'user', 'content' => $message],
            ];
        }

        $response = Http::post(env('CHATBOT_API_URL'), [
            "messages" => $messages,
        ]);

        if ($response->failed()) {
            return response()->json([
                "reply" => "⚠️ Chatbot sedang tidak tersedia."
            ]);
        }

        return response()->json($response->json());
    }
}
