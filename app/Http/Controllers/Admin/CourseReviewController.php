<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveCourseRequest;
use App\Http\Requests\Admin\RejectCourseRequest;
use App\Models\Course;
use App\Notifications\CourseApprovedNotification;
use App\Notifications\CourseRejectedNotification;
use App\Notifications\CourseSubmittedForReviewNotification;
use App\Services\AdminNotifier;
use App\Services\CourseReviewSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseReviewController extends Controller
{
    public function __construct(
        protected CourseReviewSummary $reviewSummary
    ) {}

    public function pending(Request $request): Response
    {
        $this->authorize('viewAny', Course::class);

        $user = $request->user();
        if (! $user->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPER_ADMIN])) {
            abort(403);
        }

        $courses = Course::with(['category', 'instructor'])
            ->withCount(['sections', 'lessons'])
            ->where('status', CourseStatus::PendingReview)
            ->latest('submitted_at')
            ->paginate(12);

        return Inertia::render('Admin/Courses/Pending', [
            'courses' => $courses->through(function (Course $course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'summary' => $course->summary,
                    'submitted_at' => $course->submitted_at?->toIso8601String(),
                    'instructor' => $course->instructor ? [
                        'name' => $course->instructor->name,
                        'email' => $course->instructor->email,
                    ] : null,
                    'category' => $course->category?->name,
                    'sections_count' => $course->sections_count,
                    'lessons_count' => $course->lessons_count,
                ];
            }),
        ]);
    }

    public function show(Course $course): Response|RedirectResponse
    {
        $this->authorize('review', $course);

        if (! $course->isPendingReview()) {
            return redirect()
                ->route('admin.courses.edit', $course)
                ->with('info', 'This course is not awaiting review.');
        }

        return Inertia::render('Admin/Courses/Review', $this->reviewSummary->build($course));
    }

    public function submit(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('submitForReview', $course);

        if (! $this->reviewSummary->canSubmitForReview($course)) {
            return back()->with('error', 'Add a summary, at least one section, and one lesson before submitting.');
        }

        $course->update([
            'status' => CourseStatus::PendingReview,
            'submitted_at' => now(),
            'rejection_feedback' => null,
        ]);

        app(AdminNotifier::class)->notify(new CourseSubmittedForReviewNotification($course));

        return back()->with('success', 'Course submitted for admin review.');
    }

    public function approve(ApproveCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update([
            'status' => CourseStatus::Published,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'review_summary' => $request->string('review_summary')->toString(),
            'rejection_feedback' => null,
        ]);

        $course->load('instructor');
        if ($course->instructor !== null) {
            $course->instructor->notify(new CourseApprovedNotification($course));
        }

        return redirect()
            ->route('admin.courses.review.pending')
            ->with('success', 'Course approved and published.');
    }

    public function reject(RejectCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update([
            'status' => CourseStatus::Draft,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'review_summary' => $request->input('review_summary'),
            'rejection_feedback' => $request->string('rejection_feedback')->toString(),
        ]);

        $course->load('instructor');
        if ($course->instructor !== null) {
            $course->instructor->notify(new CourseRejectedNotification($course));
        }

        return redirect()
            ->route('admin.courses.review.pending')
            ->with('success', 'Course sent back to the instructor with feedback.');
    }
}
