<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config(['domains.admin' => 'admin.elearning.local']);
    }

    public function test_instructor_can_create_course_on_admin_domain(): void
    {
        $category = Category::first();

        $response = $this->actingAs(User::where('email', 'instructor@elearning.local')->first())
            ->post('http://admin.elearning.local/courses', [
                'title' => 'Intro to PHP',
                'summary' => 'Learn PHP basics',
                'category_id' => $category->id,
                'status' => 'draft',
            ]);

        $course = Course::where('slug', 'intro-to-php')->first();

        $this->assertNotNull($course);
        $response->assertRedirect(route('admin.courses.edit', $course, absolute: false));
        $this->assertSame('instructor@elearning.local', $course->instructor->email);
    }

    public function test_instructor_cannot_edit_another_instructors_course(): void
    {
        $admin = User::where('email', 'admin@elearning.local')->first();
        $instructor = User::where('email', 'instructor@elearning.local')->first();

        $course = Course::create([
            'instructor_id' => $admin->id,
            'slug' => 'admin-only-course',
            'title' => 'Admin course',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($instructor)
            ->get('http://admin.elearning.local/courses/'.$course->id.'/edit');

        $response->assertForbidden();
    }
}
