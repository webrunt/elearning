<?php

namespace App\Services;

use App\Enums\LessonType;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;

class LessonCompletion
{
    public const WATCH_COMPLETE_PERCENT = 90;

    public function __construct(
        protected LessonAccess $lessonAccess
    ) {}

    public function isContentComplete(Lesson $lesson, LessonProgress $progress): bool
    {
        if ($lesson->type === LessonType::Article) {
            return (bool) $progress->content_completed_at;
        }

        return $progress->watched_percent >= self::WATCH_COMPLETE_PERCENT;
    }

    public function hasPassedQuiz(Lesson $lesson, int $userId): bool
    {
        return QuizAttempt::where('user_id', $userId)
            ->where('lesson_id', $lesson->id)
            ->where('passed', true)
            ->exists();
    }

    public function quizRequired(Lesson $lesson): bool
    {
        if (! $lesson->require_quiz_to_complete) {
            return false;
        }

        return $lesson->quizQuestions()->count() > 0;
    }

    public function tryMarkLessonComplete(
        Enrollment $enrollment,
        Lesson $lesson,
        LessonProgress $progress,
        int $userId
    ): bool {
        if ($progress->completed_at !== null) {
            return true;
        }

        if (! $this->isContentComplete($lesson, $progress)) {
            return false;
        }

        if ($this->quizRequired($lesson) && ! $this->hasPassedQuiz($lesson, $userId)) {
            return false;
        }

        $progress->update(['completed_at' => now()]);

        return true;
    }

    public function canAutoCompleteWithoutQuiz(Lesson $lesson, LessonProgress $progress): bool
    {
        return $this->isContentComplete($lesson, $progress) && ! $this->quizRequired($lesson);
    }
}
