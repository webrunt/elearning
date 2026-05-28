<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        $course = Course::findPublishedBySlug($slug);

        if ($user === null || ! $user->isStudent()) {
            return redirect()
                ->route('student.login')
                ->with('info', 'Sign in with a student account to enroll.');
        }

        if (! $course->isFree()) {
            return back()->with('error', 'Paid courses are not available yet.');
        }

        if ($user->isEnrolledIn($course)) {
            return redirect()
                ->route('my-learning.index')
                ->with('info', 'You are already enrolled in this course.');
        }

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        return redirect()
            ->route('my-learning.index')
            ->with('success', 'You are enrolled in '.$course->title.'.');
    }
}
