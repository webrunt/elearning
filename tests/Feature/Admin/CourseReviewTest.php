<?php

namespace Tests\Feature\Admin;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config(['domains.admin' => 'admin.elearning.local']);
    }

    public function test_instructor_can_submit_course_for_review(): void
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();
        $course = $this->courseReadyForReview($instructor);

        $response = $this->actingAs($instructor)
            ->post('http://admin.elearning.local/courses/'.$course->id.'/submit-review');

        $course->refresh();

        $response->assertRedirect();
        $this->assertSame(CourseStatus::PendingReview, $course->status);
        $this->assertNotNull($course->submitted_at);
    }

    public function test_instructor_cannot_publish_via_update(): void
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();
        $course = $this->courseReadyForReview($instructor);

        $this->actingAs($instructor)
            ->put('http://admin.elearning.local/courses/'.$course->id, [
                'title' => $course->title,
                'summary' => $course->summary,
                'status' => CourseStatus::Published->value,
            ]);

        $this->assertSame(CourseStatus::Draft, $course->fresh()->status);
    }

    public function test_admin_can_approve_pending_course(): void
    {
        $admin = User::where('email', 'admin@elearning.local')->first();
        $instructor = User::where('email', 'instructor@elearning.local')->first();
        $course = $this->courseReadyForReview($instructor);
        $course->update([
            'status' => CourseStatus::PendingReview,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->post('http://admin.elearning.local/courses/'.$course->id.'/approve', [
                'review_summary' => 'Content complete and accurate.',
            ]);

        $course->refresh();

        $response->assertRedirect(route('admin.courses.review.pending', absolute: false));
        $this->assertSame(CourseStatus::Published, $course->status);
        $this->assertSame('Content complete and accurate.', $course->review_summary);
        $this->assertSame($admin->id, $course->reviewed_by);
    }

    public function test_admin_can_reject_pending_course(): void
    {
        $admin = User::where('email', 'admin@elearning.local')->first();
        $instructor = User::where('email', 'instructor@elearning.local')->first();
        $course = $this->courseReadyForReview($instructor);
        $course->update([
            'status' => CourseStatus::PendingReview,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->post('http://admin.elearning.local/courses/'.$course->id.'/reject', [
                'rejection_feedback' => 'Add quizzes to all video lessons.',
            ]);

        $course->refresh();

        $response->assertRedirect(route('admin.courses.review.pending', absolute: false));
        $this->assertSame(CourseStatus::Draft, $course->status);
        $this->assertSame('Add quizzes to all video lessons.', $course->rejection_feedback);
    }

    public function test_instructor_cannot_access_review_queue(): void
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();

        $this->actingAs($instructor)
            ->get('http://admin.elearning.local/courses/pending')
            ->assertForbidden();
    }

    protected function courseReadyForReview(User $instructor): Course
    {
        $category = Category::first();

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'slug' => 'review-test-'.uniqid(),
            'title' => 'Review test course',
            'summary' => 'A complete summary for reviewers.',
            'status' => CourseStatus::Draft,
        ]);

        $section = Section::create([
            'course_id' => $course->id,
            'title' => 'Section 1',
            'sort_order' => 1,
        ]);

        $section->lessons()->create([
            'title' => 'Lesson 1',
            'type' => 'video',
            'sort_order' => 1,
        ]);

        return $course;
    }
}
