<?php

namespace App\Services;

use App\Enums\LessonType;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;

class CourseReviewSummary
{
    /**
     * @return array<string, mixed>
     */
    public function build(Course $course): array
    {
        $course->load([
            'category',
            'instructor',
            'reviewer',
            'sections.lessons' => fn ($query) => $query->orderBy('sort_order'),
            'sections.lessons.quizQuestions',
        ]);

        $lessons = $course->lessons;
        $videoLessons = $lessons->where('type', LessonType::Video);
        $totalSeconds = (int) $lessons->sum('duration_seconds');
        $withVideo = $videoLessons->filter(fn (Lesson $lesson) => $lesson->video_path !== null)->count();
        $withQuiz = $lessons->filter(fn (Lesson $lesson) => $lesson->quizQuestions->count() > 0)->count();

        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'summary' => $course->summary,
                'status' => $course->status->value,
                'thumbnail_url' => $course->thumbnail_path
                    ? Storage::disk('public')->url($course->thumbnail_path)
                    : null,
                'submitted_at' => $course->submitted_at?->toIso8601String(),
                'reviewed_at' => $course->reviewed_at?->toIso8601String(),
                'review_summary' => $course->review_summary,
                'rejection_feedback' => $course->rejection_feedback,
            ],
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
                'email' => $course->instructor->email,
            ] : null,
            'category' => $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->name,
            ] : null,
            'reviewer' => $course->reviewer ? [
                'name' => $course->reviewer->name,
            ] : null,
            'stats' => [
                'sections_count' => $course->sections->count(),
                'lessons_count' => $lessons->count(),
                'video_lessons' => $videoLessons->count(),
                'article_lessons' => $lessons->where('type', LessonType::Article)->count(),
                'lessons_with_video_file' => $withVideo,
                'lessons_with_quiz' => $withQuiz,
                'preview_lessons' => $lessons->where('is_preview', true)->count(),
                'total_duration_minutes' => (int) round($totalSeconds / 60),
            ],
            'curriculum' => $course->sections->map(fn ($section) => [
                'id' => $section->id,
                'title' => $section->title,
                'lessons' => $section->lessons->map(fn (Lesson $lesson) => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'type' => $lesson->type->value,
                    'is_preview' => $lesson->is_preview,
                    'has_video' => $lesson->video_path !== null,
                    'quiz_count' => $lesson->quizQuestions->count(),
                    'duration_seconds' => $lesson->duration_seconds,
                ]),
            ]),
        ];
    }

    public function canSubmitForReview(Course $course): bool
    {
        $course->loadCount(['sections', 'lessons']);

        return $course->sections_count > 0
            && $course->lessons_count > 0
            && $course->summary !== null
            && trim($course->summary) !== '';
    }
}
