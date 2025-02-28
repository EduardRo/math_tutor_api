<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AI\AITutorService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    protected $aiServices;

    public function __construct(AITutorService $aiServices)
    {
        $this->aiServices = $aiServices;
    }

    /**
     * Process a math question
     */
    public function processQuestion(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'context' => 'nullable|string',
            'complexity' => 'nullable|string|in:simple,complex',
            'student_id' => 'required|exists:students,id'
        ]);

        $response = $this->aiService->processStudentQuery(
            $validated['question'],
            $validated['context'] ?? '',
            $validated['complexity'] ?? 'simple'
        );

        return response()->json($response);
    }

    /**
     * Process student engagement data
     */
    public function processEngagement(Request $request)
    {
        $validated = $request->validate([
            'image_data' => 'required|string', // Base64 encoded image
            'student_id' => 'required|exists:students,id',
            'lesson_id' => 'required|exists:lessons,id'
        ]);

        $engagement = $this->aiService->processStudentEngagement(
            $validated['image_data']
        );

        // Store engagement metrics in database
        // This would be implemented in a real application

        return response()->json($engagement);
    }

    /**
     * Get avatar reaction
     */
    public function getAvatarReaction(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'lesson_id' => 'required|exists:lessons,id',
            'engagement_data' => 'required|array'
        ]);

        $reaction = $this->aiService->generateAvatarReaction(
            $validated['engagement_data'],
            ['lesson_id' => $validated['lesson_id']]
        );

        return response()->json($reaction);
    }


}
