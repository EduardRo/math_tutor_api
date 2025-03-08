<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    protected $baseUrl = 'http://localhost:11434/api/generate';

    public function generateResponse($prompt, $model = 'mistral')
    {
        $response = Http::post($this->baseUrl, [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false // Set to true if you want streamed responses
        ]);

        return $response->json();
    }
}
