<?php

namespace Tests\Feature\Student;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogEnrollmentTest extends TestCase
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
    }

    public function test_guest_can_browse_published_catalog(): void
    {
        $course = $this->publishedCourse();

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Student/Catalog/Index')
                ->has('courses.data', 1)
                ->where('courses.data.0.slug', $course->slug));

        $this->get(route('catalog.show', $course->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Student/Catalog/Show')
                ->where('course.slug', $course->slug)
                ->where('can_enroll', false)
                ->where('login_required', true));
    }

    public function test_draft_course_not_visible_in_catalog(): void
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();

        Course::create([
            'instructor_id' => $instructor->id,
            'slug' => 'secret-draft',
            'title' => 'Draft only',
            'status' => CourseStatus::Draft,
        ]);

        $this->get(route('catalog.show', 'secret-draft'))
            ->assertNotFound();
    }

    public function test_student_can_enroll_in_free_course(): void
    {
        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourse();

        $response = $this->actingAs($student)
            ->post(route('enrollments.store', $course->slug));

        $response->assertRedirect(route('my-learning.index'));

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_student_sees_enrolled_courses_on_my_learning(): void
    {
        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourse();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('my-learning.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Student/MyLearning/Index')
                ->has('enrollments', 1)
                ->where('enrollments.0.course.slug', $course->slug)
                ->where('enrollments.0.progress_percent', 0));
    }

    protected function publishedCourse(): Course
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();
        $category = Category::first();

        return Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'slug' => 'catalog-test-course',
            'title' => 'Catalog Test Course',
            'summary' => '<p>Test summary</p>',
            'status' => CourseStatus::Published,
            'price' => null,
        ]);
    }
}
