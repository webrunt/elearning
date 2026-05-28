<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $featuredCourses = Course::published()
            ->with(['category', 'instructor'])
            ->withCount('lessons')
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(fn (Course $course) => [
                'slug' => $course->slug,
                'title' => $course->title,
                'summary' => $course->summary,
                'thumbnail_url' => $course->thumbnail_path
                    ? Storage::disk('public')->url($course->thumbnail_path)
                    : null,
                'lessons_count' => $course->lessons_count,
                'category' => $course->category?->name,
                'instructor' => $course->instructor?->name,
                'is_enrolled' => $user !== null && $user->isStudent() && $user->isEnrolledIn($course),
            ]);

        return Inertia::render('Home', [
            'featured_courses' => $featuredCourses,
        ]);
    }
}
