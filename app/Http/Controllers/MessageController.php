<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function ask(Request $request)
    {
        $prompt = $request->input('prompt');

        if (!$prompt) {
            return response()->json(['error' => 'No prompt provided'], 400);
        }

        // Check if user is authenticated
        //$user = Auth::check() ? Auth::user() : null;
        $user = Auth::guard('api')->user();

        $ip = $request->ip();

        // Count how many messages this IP has today
        $todayCount = Message::whereDate('created_at', now()->toDateString())
            ->where('ip_address', $ip)
            ->count();

        if ($user) {
            // Authenticated user
            $userId = $user->id;
            $userEmail = $user->email;
            $userName = $user->name;
            $model = 'deepseek/deepseek-chat-v3-0324';
            // Use different limits or models if needed
        } else {
            // Guest
            // $ip = $request->ip();
            // Use IP-based limits or assign guest model
            $userName = "Guest";
            $userId = 0;
            $userEmail = "no-email";
            $model = 'qwen/qwen3-32b:free';
            if ($todayCount >= 3) {
                return response()->json([
                    'message' => 'You have reached your limit of 3 questions for today.',
                ], 429);
            }
        }

        // SYSTEM message: this sets the tone
        $systemPrompt = "You are a Christian spiritual advisor (like a compassionate priest). "
            . "Always offer a warm, encouraging answer from a religious perspective. "
            . "Include 5 relevant Bible verses (book + chapter:verse) with each answer, "
            . "preferably ones that offer hope, strength, or understanding. Format verses with *text*.";

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                'Content-Type' => 'application/json',
                'HTTP-Referer' => 'your-domain.com', // Optional but recommended by OpenRouter
            ])
                ->timeout(160)
                ->post('https://openrouter.ai/api/v1/chat/completions', $payload);

            if (!$response->successful()) {
                Log::error('OpenRouter error: ' . $response->body());
                return response()->json(['error' => 'AI model call failed'], 500);
            }

            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? 'No response from AI.';

            // Save to database
            $msg = Message::create([
                'user_id' => $userId,
                'ip_address' => $ip,
                'prompt' => $prompt,
                'response' => trim($reply),
                'source' => $model,
                'fallback_used' => false,
            ]);

            return response()->json($msg);
        } catch (\Exception $e) {
            Log::error('Exception in ask(): ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }









    public function answer(Request $request)
    {
        $prompt = "I am afraid of the future";

        if (!$prompt) {
            return response()->json(['error' => 'No prompt provided'], 400);
        }

        // SYSTEM message: this sets the tone
        $systemPrompt = "You are a Christian spiritual advisor (like a compassionate priest). "
            . "Always offer a warm, encouraging answer from a religious perspective. "
            . "Include 5 relevant Bible verses (book + chapter:verse) with each answer, "
            . "preferably ones that offer hope, strength, or understanding. Format verses with *text*.";

        $payload = [
            'model' => 'deepseek/deepseek-chat-v3-0324',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                'Content-Type' => 'application/json',
                'HTTP-Referer' => 'your-domain.com', // Optional but recommended by OpenRouter
            ])
                ->timeout(160)
                ->post('https://openrouter.ai/api/v1/chat/completions', $payload);

            if (!$response->successful()) {
                Log::error('OpenRouter error: ' . $response->body());
                return response()->json(['error' => 'AI model call failed'], 500);
            }

            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? 'No response from AI.';
            //print_r($reply);

            //print_r($request->ip());
            $ip = $request->ip();
            //echo  $ip;
            // Save to database
            $msg = Message::create([
                'prompt' => $prompt,
                'response' => trim($reply),
                'source' => 'deepseek',
                'fallback_used' => false,
            ]);
            //print_r($msg);
            //print_r($request->ip());
            echo  $ip;
            //return response()->json($msg);
        } catch (\Exception $e) {
            Log::error('Exception in ask(): ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}
