<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson; // Assuming you have a Lesson model
use App\Http\Resources\LessonResource; // Optional: Use API Resources for formatting

class LessonController extends Controller
{
    /**
     * Display the specified lesson.
     *
     * @param  \App\Models\Lesson $lesson // Route Model Binding
     * @return \Illuminate\Http\JsonResponse | LessonResource
     */
    public function show(Lesson $lesson)
    {
        // Ensure the lesson includes necessary data for the frontend
        // You might need to load relationships if practice problems are separate
        // $lesson->load('practiceProblems'); // Example if using Eloquent relationships

        // Option 1: Return Lesson directly (adjust fields in Model's $casts/$hidden/$visible)
        // You need to make sure your Lesson model retrieves data in the format
        // expected by LessonView.vue (title, math_topic, difficulty_level, content, practice_problems array)
        // Make sure 'practice_problems' is cast to an array/collection in your Lesson model if stored as JSON
         return response()->json($lesson);

        // Option 2: Use an API Resource for transformation (Recommended for complex data)
        // return new LessonResource($lesson);
    }
}
