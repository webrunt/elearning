<?php

namespace App\Http\Controllers\Student;

use App\Enums\CourseReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreCourseReviewRequest;
use App\Models\Course;
use App\Models\CourseReview;
use App\Notifications\StudentReviewSubmittedNotification;
use App\Services\AdminNotifier;
use Illuminate\Http\RedirectResponse;

class CourseReviewController extends Controller
{
    public function store(StoreCourseReviewRequest $request, string $slug): RedirectResponse
    {
        $course = Course::findPublishedBySlug($slug);

        $existing = CourseReview::query()
            ->where('course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing !== null) {
            if (! $request->user()->can('update', $existing)) {
                return back()->with('error', 'You cannot edit this review.');
            }

            $existing->update([
                'rating' => (int) $request->input('rating'),
                'body' => $request->input('body'),
                'status' => CourseReviewStatus::Pending,
                'moderation_note' => null,
                'moderated_by' => null,
                'moderated_at' => null,
            ]);

            $review = $existing;
        } else {
            $review = CourseReview::create([
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
                'rating' => (int) $request->input('rating'),
                'body' => $request->input('body'),
                'status' => CourseReviewStatus::Pending,
            ]);
        }

        $review->load('course');
        app(AdminNotifier::class)->notify(new StudentReviewSubmittedNotification($review));

        return back()->with('success', 'Thank you! Your review was submitted and will appear after moderation.');
    }
}
