<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\CourseCurriculum;
use App\Services\LessonAccess;
use App\Services\LessonCompletion;
use App\Services\LessonQuizSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    public function __construct(
        protected LessonAccess $lessonAccess,
        protected CourseCurriculum $curriculum,
        protected LessonCompletion $lessonCompletion,
        protected LessonQuizSession $quizSession
    ) {}

    public function show(Request $request, string $slug, Lesson $lesson): Response
    {
        $this->authorize('learn', $lesson);

        $course = Course::findPublishedBySlug($slug);
        $lesson->loadMissing('section.course');

        if ((int) $lesson->section->course_id !== (int) $course->id) {
            abort(404);
        }

        $user = $request->user();
        $enrollment = $this->lessonAccess->enrollmentFor($user, $lesson);
        $canView = $this->lessonAccess->canView($user, $lesson);
        $canTrack = $user !== null && $this->lessonAccess->canTrackProgress($user, $lesson);

        $progress = null;
        if ($enrollment !== null) {
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
        }

        $quizRequired = $this->lessonCompletion->quizRequired($lesson);
        $hasPassedQuiz = $user !== null && $this->lessonCompletion->hasPassedQuiz($lesson, $user->id);
        $contentComplete = $progress !== null
            && $this->lessonCompletion->isContentComplete($lesson, $progress);
        $lessonComplete = $progress !== null && $progress->completed_at !== null;

        $completedLessonIds = [];
        if ($enrollment !== null) {
            $completedLessonIds = $enrollment->lessonProgress()
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')
                ->all();
        }

        $curriculumLessons = $this->curriculum->orderedLessons($course);

        return Inertia::render('Student/Learn/Show', [
            'course' => [
                'id' => $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
            ],
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'type' => $lesson->type->value,
                'summary' => $lesson->summary,
                'content' => $lesson->content,
                'duration_seconds' => $lesson->duration_seconds,
                'is_preview' => $lesson->is_preview,
                'require_quiz_to_complete' => $lesson->require_quiz_to_complete,
                'quiz_pass_percent' => $lesson->quiz_pass_percent,
                'has_quiz' => $lesson->quizQuestions()->count() > 0,
                'video_url' => $lesson->video_path && $canView
                    ? route('learn.lessons.video', $lesson)
                    : null,
            ],
            'curriculum' => $curriculumLessons->map(fn (Lesson $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type->value,
                'is_preview' => $item->is_preview,
                'is_completed' => in_array($item->id, $completedLessonIds, true),
                'is_current' => $item->id === $lesson->id,
                'href' => route('learn.show', ['slug' => $course->slug, 'lesson' => $item->id]),
            ])->values()->all(),
            'progress' => $progress ? [
                'last_position_seconds' => $progress->last_position_seconds,
                'watched_percent' => $progress->watched_percent,
                'content_complete' => $contentComplete,
                'completed_at' => $progress->completed_at?->toIso8601String(),
            ] : null,
            'can_track_progress' => $canTrack,
            'is_enrolled' => $enrollment !== null,
            'quiz' => [
                'required' => $quizRequired,
                'passed' => $hasPassedQuiz,
                'pass_percent' => $lesson->quiz_pass_percent > 0 ? $lesson->quiz_pass_percent : 70,
                'questions_per_attempt' => LessonQuizSession::QUESTIONS_PER_ATTEMPT,
            ],
            'lesson_complete' => $lessonComplete,
            'next_lesson' => $this->nextLessonPayload($course, $lesson),
            'previous_lesson' => $this->previousLessonPayload($course, $lesson),
            'enroll_url' => route('enrollments.store', $course->slug),
        ]);
    }

    public function continue(Request $request, string $slug): RedirectResponse
    {
        $course = Course::findPublishedBySlug($slug);
        $user = $request->user();

        if ($user === null || ! $user->isStudent()) {
            return redirect()->route('student.login');
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($enrollment === null) {
            return redirect()->route('catalog.show', $course->slug);
        }

        $lesson = $this->curriculum->firstIncompleteLesson($course, $enrollment)
            ?? $this->curriculum->firstLesson($course);

        if ($lesson === null) {
            return redirect()->route('catalog.show', $course->slug);
        }

        return redirect()->route('learn.show', ['slug' => $course->slug, 'lesson' => $lesson->id]);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function nextLessonPayload(Course $course, Lesson $lesson): ?array
    {
        $next = $this->curriculum->nextLesson($course, $lesson);

        if ($next === null) {
            return null;
        }

        return [
            'id' => $next->id,
            'title' => $next->title,
            'href' => route('learn.show', ['slug' => $course->slug, 'lesson' => $next->id]),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function previousLessonPayload(Course $course, Lesson $lesson): ?array
    {
        $previous = $this->curriculum->previousLesson($course, $lesson);

        if ($previous === null) {
            return null;
        }

        return [
            'id' => $previous->id,
            'title' => $previous->title,
            'href' => route('learn.show', ['slug' => $course->slug, 'lesson' => $previous->id]),
        ];
    }
}
