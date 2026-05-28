<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Course::class);

        $user = $request->user();
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $categoryId = $request->input('category_id');

        $courses = Course::with(['category', 'instructor'])->withCount('lessons')
            ->when($user->hasRole(User::ROLE_INSTRUCTOR) && ! $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]), function ($query) use ($user) {
                $query->where('instructor_id', $user->id);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($categoryId !== null && $categoryId !== '', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses->through(fn (Course $course) => $this->listCourse($course)),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category_id' => $categoryId,
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'statuses' => CourseStatus::labels(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Course::class);

        return Inertia::render('Admin/Courses/Create', $this->formOptions($request));
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $instructorId = $this->resolveInstructorId($request);
        $slug = UniqueSlug::forModel(new Course, $request->string('title')->toString());

        $course = Course::create([
            'instructor_id' => $instructorId,
            'category_id' => $request->input('category_id'),
            'slug' => $slug,
            'title' => $request->string('title')->toString(),
            'summary' => $request->input('summary'),
            'status' => $request->input('status'),
            'thumbnail_path' => $this->storeThumbnail($request),
        ]);

        return redirect()
            ->route('admin.courses.edit', $course)
            ->with('success', 'Course created. Add sections and lessons below.');
    }

    public function edit(Request $request, Course $course): Response
    {
        $this->authorize('update', $course);

        $course->load([
            'category',
            'instructor',
            'sections.lessons' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        return Inertia::render('Admin/Courses/Edit', array_merge($this->formOptions($request), [
            'course' => $this->detailCourse($course),
        ]));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $title = $request->string('title')->toString();
        $slug = $course->slug;

        if ($title !== $course->title) {
            $slug = UniqueSlug::forModel(new Course, $title);
        }

        $data = [
            'instructor_id' => $this->resolveInstructorId($request, $course),
            'category_id' => $request->input('category_id'),
            'slug' => $slug,
            'title' => $title,
            'summary' => $request->input('summary'),
            'status' => $request->input('status'),
        ];

        $thumbnail = $this->storeThumbnail($request);
        if ($thumbnail !== null) {
            if ($course->thumbnail_path !== null) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }
            $data['thumbnail_path'] = $thumbnail;
        }

        $course->update($data);

        return back()->with('success', 'Course saved.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        if ($course->thumbnail_path !== null) {
            Storage::disk('public')->delete($course->thumbnail_path);
        }

        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(Request $request): array
    {
        $user = $request->user();
        $canPickInstructor = $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $instructors = [];
        if ($canPickInstructor) {
            $instructors = User::role(User::ROLE_INSTRUCTOR)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn (User $instructor) => [
                    'id' => $instructor->id,
                    'name' => $instructor->name,
                    'email' => $instructor->email,
                ])
                ->values()
                ->all();
        }

        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'statuses' => CourseStatus::labels(),
            'instructors' => $instructors,
            'can_pick_instructor' => $canPickInstructor,
        ];
    }

    protected function resolveInstructorId(Request $request, ?Course $course = null): int
    {
        $user = $request->user();

        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]) && $request->filled('instructor_id')) {
            return (int) $request->input('instructor_id');
        }

        if ($course !== null && $user->hasRole(User::ROLE_INSTRUCTOR)) {
            return (int) $course->instructor_id;
        }

        return (int) $user->id;
    }

    protected function storeThumbnail(Request $request): ?string
    {
        if (! $request->hasFile('thumbnail')) {
            return null;
        }

        return $request->file('thumbnail')->store('courses/thumbnails', 'public');
    }

    /**
     * @return array<string, mixed>
     */
    protected function listCourse(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'status' => $course->status->value,
            'status_label' => CourseStatus::labels()[$course->status->value] ?? $course->status->value,
            'thumbnail_url' => $course->thumbnail_path
                ? Storage::disk('public')->url($course->thumbnail_path)
                : null,
            'category' => $course->category ? ['id' => $course->category->id, 'name' => $course->category->name] : null,
            'instructor' => $course->instructor ? ['id' => $course->instructor->id, 'name' => $course->instructor->name] : null,
            'lessons_count' => $course->lessons_count,
            'updated_at' => $course->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function detailCourse(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'summary' => $course->summary,
            'status' => $course->status->value,
            'category_id' => $course->category_id,
            'instructor_id' => $course->instructor_id,
            'thumbnail_url' => $course->thumbnail_path
                ? Storage::disk('public')->url($course->thumbnail_path)
                : null,
            'sections' => $course->sections->map(fn ($section) => [
                'id' => $section->id,
                'title' => $section->title,
                'sort_order' => $section->sort_order,
                'lessons' => $section->lessons->map(fn ($lesson) => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'type' => $lesson->type->value,
                    'sort_order' => $lesson->sort_order,
                    'is_preview' => $lesson->is_preview,
                ]),
            ]),
        ];
    }
}
