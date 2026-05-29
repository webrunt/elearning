<?php

namespace Tests\Feature;

use App\Enums\CourseReviewStatus;
use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\CourseSubmittedForReviewNotification;
use App\Notifications\StudentReviewModeratedNotification;
use App\Notifications\StudentReviewSubmittedNotification;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Phase4TrustAndAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleAndPermissionSeeder::class,
            DemoUserSeeder::class,
            CategorySeeder::class,
        ]);

        config(['domains.admin' => 'admin.elearning.local']);
    }

    public function test_enrolled_student_can_submit_course_review(): void
    {
        Notification::fake();

        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourse();
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        $response = $this->actingAs($student)
            ->post(route('course-reviews.store', $course->slug), [
                'rating' => 4,
                'body' => 'Great intro course.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('course_reviews', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 4,
            'status' => CourseReviewStatus::Pending->value,
        ]);

        $admin = User::where('email', 'admin@elearning.local')->first();
        Notification::assertSentTo($admin, StudentReviewSubmittedNotification::class);
    }

    public function test_admin_can_approve_student_review(): void
    {
        Notification::fake();

        $admin = User::where('email', 'admin@elearning.local')->first();
        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourse();

        $review = CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 5,
            'body' => 'Loved it.',
            'status' => CourseReviewStatus::Pending,
        ]);

        $response = $this->actingAs($admin)
            ->post('http://admin.elearning.local/reviews/'.$review->id.'/approve');

        $response->assertRedirect();
        $this->assertSame(CourseReviewStatus::Approved, $review->fresh()->status);

        Notification::assertSentTo($student, StudentReviewModeratedNotification::class);
    }

    public function test_approved_reviews_appear_on_course_detail(): void
    {
        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourse();

        CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 5,
            'body' => 'Excellent.',
            'status' => CourseReviewStatus::Approved,
        ]);

        $this->get(route('catalog.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('reviews_summary.count', 1)
                ->where('reviews_summary.average_rating', 5)
                ->has('reviews', 1)
                ->where('reviews.0.body', 'Excellent.'));
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = User::where('email', 'admin@elearning.local')->first();
        $student = User::where('email', 'student@elearning.local')->first();

        $this->actingAs($admin)
            ->patch('http://admin.elearning.local/users/'.$student->id, [
                'role' => User::ROLE_INSTRUCTOR,
            ])
            ->assertRedirect();

        $this->assertTrue($student->fresh()->hasRole(User::ROLE_INSTRUCTOR));
    }

    public function test_course_submit_notifies_admins(): void
    {
        Notification::fake();

        $instructor = User::where('email', 'instructor@elearning.local')->first();
        $admin = User::where('email', 'admin@elearning.local')->first();
        $category = Category::first();

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'slug' => 'notify-course',
            'title' => 'Notify Course',
            'summary' => '<p>Summary</p>',
            'status' => CourseStatus::Draft,
        ]);

        $section = $course->sections()->create(['title' => 'Section 1', 'sort_order' => 0]);
        $section->lessons()->create([
            'title' => 'Lesson 1',
            'type' => 'article',
            'sort_order' => 0,
        ]);

        $this->actingAs($instructor)
            ->post('http://admin.elearning.local/courses/'.$course->id.'/submit-review');

        Notification::assertSentTo($admin, CourseSubmittedForReviewNotification::class);
    }

    protected function publishedCourse(): Course
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();
        $category = Category::first();

        return Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'slug' => 'phase-4-course-'.uniqid(),
            'title' => 'Phase 4 Course',
            'summary' => 'Summary',
            'status' => CourseStatus::Published,
            'price' => 0,
        ]);
    }
}
