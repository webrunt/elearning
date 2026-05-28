<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    /**
     * @return array<string, mixed>
     */
    protected function catalogCourse(Course $course, ?User $user = null): array
    {
        $isEnrolled = $user !== null && $user->isEnrolledIn($course);

        return [
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'summary' => $course->summary,
            'thumbnail_url' => $course->thumbnail_path
                ? Storage::disk('public')->url($course->thumbnail_path)
                : null,
            'price' => $course->price,
            'is_free' => $course->isFree(),
            'lessons_count' => $course->lessons_count ?? 0,
            'category' => $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->name,
                'slug' => $course->category->slug,
            ] : null,
            'instructor' => $course->instructor ? [
                'name' => $course->instructor->name,
            ] : null,
            'is_enrolled' => $isEnrolled,
        ];
    }

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $categorySlug = $request->string('category')->toString();
        $user = $request->user();

        $courses = Course::published()
            ->with(['category', 'instructor'])
            ->withCount('lessons')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('summary', 'like', '%'.$search.'%');
                });
            })
            ->when($categorySlug !== '', function ($query) use ($categorySlug) {
                $query->whereHas('category', function ($categoryQuery) use ($categorySlug) {
                    $categoryQuery->where('slug', $categorySlug);
                });
            })
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Student/Catalog/Index', [
            'courses' => $courses->through(fn (Course $course) => $this->catalogCourse($course, $user)),
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
            ],
            'categories' => Category::orderBy('sort_order')->get(['id', 'name', 'slug']),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $course = Course::findPublishedBySlug($slug);
        $user = $request->user();

        $course->load([
            'category',
            'instructor',
            'sections.lessons' => fn ($query) => $query->orderBy('sort_order'),
        ]);
        $course->loadCount('lessons');

        $enrollment = null;
        $progressPercent = 0;

        if ($user !== null && $user->isStudent()) {
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment !== null) {
                $progressPercent = app(\App\Services\EnrollmentProgress::class)->percent($enrollment);
            }
        }

        return Inertia::render('Student/Catalog/Show', [
            'course' => array_merge($this->catalogCourse($course, $user), [
                'sections' => $course->sections->map(fn ($section) => [
                    'id' => $section->id,
                    'title' => $section->title,
                    'lessons' => $section->lessons->map(fn ($lesson) => [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'type' => $lesson->type->value,
                        'is_preview' => $lesson->is_preview,
                    ]),
                ]),
            ]),
            'progress_percent' => $progressPercent,
            'can_enroll' => $user !== null
                && $user->isStudent()
                && $enrollment === null
                && $course->isFree(),
            'login_required' => $user === null,
        ]);
    }
}
