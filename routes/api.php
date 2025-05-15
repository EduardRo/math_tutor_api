<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OllamaController;
use App\Http\Controllers\API\AIController;
use App\Http\Controllers\API\LessonController;

use App\Http\Controllers\MessageController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//});


// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// OAuth2 routes
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

/* Ollama

Route::post('/ollama/chat', function (Request $request, OllamaService $ollamaService) {
    $prompt = $request->input('prompt');
    $response = $ollamaService->generateResponse($prompt);

    return response()->json($response);
});
*/
//---------------------modifications-----------------------
// Engagement Processing & Avatar Reaction (using AIController)
//Route::post('/ai/engagement', [AIController::class, 'processEngagement']);
//Route::post('/ai/avatar-reaction', [AIController::class, 'getAvatarReaction']);

// --- Lesson Data Route ---
//Route::get('/lessons/{lesson}', [LessonController::class, 'show']); // Added show method route


Route::get('/answer', [MessageController::class, 'answer']);

//Route::post('/ask', [MessageController::class, 'ask']);

//Route::middleware('auth:api')->post('/ask', [MessageController::class, 'ask']);
Route::post('/ask', [MessageController::class, 'ask']);
