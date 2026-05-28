<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Services\EnrollmentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MyLearningController extends Controller
{
    public function __construct(
        protected EnrollmentProgress $enrollmentProgress
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->with(['course.category', 'course.instructor'])
            ->withCount([
                'lessonProgress as completed_lessons_count' => function ($query) {
                    $query->whereNotNull('completed_at');
                },
            ])
            ->latest('enrolled_at')
            ->get();

        $enrollments->load(['course' => fn ($query) => $query->withCount('lessons')]);

        return Inertia::render('Student/MyLearning/Index', [
            'enrollments' => $enrollments->map(function (Enrollment $enrollment) {
                $course = $enrollment->course;
                $totalLessons = (int) $course->lessons_count;
                $completedLessons = (int) $enrollment->completed_lessons_count;
                $progressPercent = $this->enrollmentProgress->percent($enrollment);

                return [
                    'id' => $enrollment->id,
                    'enrolled_at' => $enrollment->enrolled_at->toIso8601String(),
                    'progress_percent' => $progressPercent,
                    'completed_lessons_count' => $completedLessons,
                    'total_lessons_count' => $totalLessons,
                    'course' => [
                        'id' => $course->id,
                        'slug' => $course->slug,
                        'title' => $course->title,
                        'summary' => $course->summary,
                        'thumbnail_url' => $course->thumbnail_path
                            ? Storage::disk('public')->url($course->thumbnail_path)
                            : null,
                        'category' => $course->category ? [
                            'name' => $course->category->name,
                        ] : null,
                        'instructor' => $course->instructor ? [
                            'name' => $course->instructor->name,
                        ] : null,
                    ],
                ];
            }),
        ]);
    }
}
