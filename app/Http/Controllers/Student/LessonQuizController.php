<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitLessonQuizRequest;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\LessonAccess;
use App\Services\LessonCompletion;
use App\Services\LessonQuizSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonQuizController extends Controller
{
    public function __construct(
        protected LessonAccess $lessonAccess,
        protected LessonQuizSession $quizSession,
        protected LessonCompletion $lessonCompletion
    ) {}

    public function show(Request $request, Lesson $lesson): JsonResponse
    {
        $this->authorize('trackProgress', $lesson);

        if ($lesson->quizQuestions()->count() === 0) {
            return response()->json(['questions' => []]);
        }

        return response()->json([
            'questions' => $this->quizSession->randomQuestionsForClient($lesson),
            'pass_percent' => $lesson->quiz_pass_percent > 0 ? $lesson->quiz_pass_percent : 70,
        ]);
    }

    public function store(SubmitLessonQuizRequest $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user();
        $enrollment = $this->lessonAccess->enrollmentFor($user, $lesson);

        if ($enrollment === null) {
            return response()->json(['message' => 'Enrollment required.'], 403);
        }

        $progress = LessonProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'last_position_seconds' => 0,
                'watched_percent' => 0,
            ]
        );

        if (! $this->lessonCompletion->isContentComplete($lesson, $progress)) {
            return response()->json([
                'message' => 'Complete the lesson content before taking the quiz.',
            ], 422);
        }

        $result = $this->quizSession->gradeAttempt($lesson, $request->input('answers', []));

        $this->quizSession->storeAttempt(
            $lesson,
            $user->id,
            $enrollment->id,
            $result['score'],
            $result['passed'],
            $result['graded_answers']
        );

        if ($result['passed']) {
            $this->lessonCompletion->tryMarkLessonComplete($enrollment, $lesson, $progress, $user->id);
            $progress->refresh();
        }

        return response()->json([
            'score' => $result['score'],
            'passed' => $result['passed'],
            'pass_percent' => $result['pass_percent'],
            'lesson_complete' => $progress->completed_at !== null,
        ]);
    }
}
