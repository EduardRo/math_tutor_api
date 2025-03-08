<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// OAuth2 routes
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

// Ollama

Route::post('/ollama/chat', function (Request $request, OllamaService $ollamaService) {
    $prompt = $request->input('prompt');
    $response = $ollamaService->generateResponse($prompt);

    return response()->json($response);
});
