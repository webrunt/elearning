<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateLessonProgressRequest;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\LessonAccess;
use App\Services\LessonCompletion;
use Illuminate\Http\JsonResponse;

class LessonProgressController extends Controller
{
    public function __construct(
        protected LessonAccess $lessonAccess,
        protected LessonCompletion $lessonCompletion
    ) {}

    public function update(UpdateLessonProgressRequest $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user();
        $enrollment = $this->lessonAccess->enrollmentFor($user, $lesson);

        if ($enrollment === null) {
            return response()->json(['message' => 'Enrollment required to save progress.'], 403);
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

        $data = [];

        if ($request->has('last_position_seconds')) {
            $data['last_position_seconds'] = (int) $request->input('last_position_seconds');
        }

        if ($request->has('watched_percent')) {
            $watched = (int) $request->input('watched_percent');
            $data['watched_percent'] = $watched > $progress->watched_percent ? $watched : $progress->watched_percent;
        }

        if ($request->boolean('mark_content_complete')) {
            $data['content_completed_at'] = now();
        }

        if (count($data) > 0) {
            $progress->update($data);
            $progress->refresh();
        }

        if ($this->lessonCompletion->canAutoCompleteWithoutQuiz($lesson, $progress)) {
            $this->lessonCompletion->tryMarkLessonComplete($enrollment, $lesson, $progress, $user->id);
            $progress->refresh();
        }

        return response()->json([
            'progress' => [
                'last_position_seconds' => $progress->last_position_seconds,
                'watched_percent' => $progress->watched_percent,
                'content_complete' => $this->lessonCompletion->isContentComplete($lesson, $progress),
                'completed_at' => $progress->completed_at?->toIso8601String(),
            ],
            'lesson_complete' => $progress->completed_at !== null,
        ]);
    }
}
