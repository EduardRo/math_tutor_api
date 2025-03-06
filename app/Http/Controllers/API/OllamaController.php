<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use App\Services\ClaudeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// De modificat pentru DeepSeek

class OllamaController extends Controller
{
    protected $ollamaService;
    protected $claudeService;

    public function __construct(OllamaService $ollamaService, ClaudeService $claudeService)
    {
        $this->ollamaService = $ollamaService;
        $this->claudeService = $claudeService;
    }

    public function handleOllamaQuestion(Request $request)
    {
        $question = $request->input('question');
        $context = $request->input('context', []);

        // Build appropriate prompt with context
        $prompt = $this->buildMathTutorPrompt($question, $context);

        try {
            $response = $this->ollamaService->generateResponse($prompt);

            if (!$response) {
                throw new \Exception('Ollama failed to generate a response');
            }

            return response()->json([
                'response' => $response['response'] ?? 'No response generated',
                'source' => 'Ollama',
                'suggested_expression' => 'pleased',
                'suggested_animation' => 'hand_gesture',
                'suggested_message' => 'I hope that helps! Do you have any other questions?'
            ]);

        } catch (\Exception $e) {
            Log::error('Ollama error: ' . $e->getMessage());

            return response()->json([
                'error' => 'ollama_failure',
                'message' => 'Failed to generate response from Ollama'
            ], 500);
        }
    }

    public function handleClaudeQuestion(Request $request)
    {
        $question = $request->input('question');
        $context = $request->input('context', []);
        $isFallback = $request->input('is_fallback', false);

        // Build appropriate prompt with context
        $prompt = $this->buildMathTutorPrompt($question, $context, true);

        try {
            $response = $this->claudeService->generateResponse($prompt);

            return response()->json([
                'response' => $response,
                'source' => 'Claude',
                'suggested_expression' => $isFallback ? 'relieved' : 'pleased',
                'suggested_animation' => $isFallback ? 'nod' : 'hand_gesture',
                'suggested_message' => $isFallback
                    ? 'That was a challenging question! Does my answer help?'
                    : 'I\'ve thought carefully about this. Does that answer your question?'
            ]);

        } catch (\Exception $e) {
            Log::error('Claude API error: ' . $e->getMessage());

            return response()->json([
                'error' => 'claude_failure',
                'message' => 'Failed to generate response from Claude API'
            ], 500);
        }
    }

    private function buildMathTutorPrompt($question, $context, $isClaudePrompt = false)
    {
        // Extract context variables
        $lessonTitle = $context['lesson_title'] ?? 'Math Lesson';
        $mathTopic = $context['math_topic'] ?? 'Mathematics';
        $difficultyLevel = $context['difficulty_level'] ?? 'Unknown';
        $lessonContent = $context['current_lesson_content'] ?? '';

        // Basic prompt structure
        $basePrompt = <<<EOT
You are an enthusiastic and patient math tutor helping a student with a {$difficultyLevel} level {$mathTopic} lesson called "{$lessonTitle}".

LESSON CONTENT:
{$lessonContent}

The student has asked: "{$question}"

Provide a clear, step-by-step explanation that is appropriate for their level. Use simple language but be mathematically precise. Include examples if helpful.
EOT;

        // For Claude, we can add more detailed instructions
        if ($isClaudePrompt) {
            $basePrompt .= <<<EOT

Guide the student to the answer rather than just giving it directly. If they're asking for the answer to a problem, show all work clearly so they understand the process.

If they seem confused, identify the specific concept they might be struggling with and offer a different explanation approach. Use analogies when appropriate.

Format mathematical notation clearly using markdown. For equations, use * for multiplication, / for division, ^ for exponents, and sqrt() for square roots.
EOT;
        }

        return $basePrompt;
    }
}
