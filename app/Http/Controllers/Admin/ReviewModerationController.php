<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\CourseReview;
use App\Notifications\StudentReviewModeratedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewModerationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('moderate', CourseReview::class);

        $reviews = CourseReview::query()
            ->with(['course', 'user'])
            ->where('status', CourseReviewStatus::Pending)
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Reviews/Moderation', [
            'reviews' => $reviews->through(fn (CourseReview $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'body' => $review->body,
                'created_at' => $review->created_at?->toIso8601String(),
                'course' => [
                    'id' => $review->course->id,
                    'title' => $review->course->title,
                    'slug' => $review->course->slug,
                ],
                'user' => [
                    'name' => $review->user->name,
                    'email' => $review->user->email,
                ],
            ]),
        ]);
    }

    public function approve(Request $request, CourseReview $review): RedirectResponse
    {
        $this->authorize('moderate', CourseReview::class);

        $review->update([
            'status' => CourseReviewStatus::Approved,
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
            'moderation_note' => null,
        ]);

        $review->load('course');
        $review->user->notify(new StudentReviewModeratedNotification($review, approved: true));

        return back()->with('success', 'Review approved and published.');
    }

    public function reject(Request $request, CourseReview $review): RedirectResponse
    {
        $this->authorize('moderate', CourseReview::class);

        $request->validate([
            'moderation_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $review->update([
            'status' => CourseReviewStatus::Rejected,
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
            'moderation_note' => $request->input('moderation_note'),
        ]);

        $review->load('course');
        $review->user->notify(new StudentReviewModeratedNotification($review, approved: false));

        return back()->with('success', 'Review rejected.');
    }
}
