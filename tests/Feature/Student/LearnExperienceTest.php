<?php

namespace Tests\Feature\Student;

use App\Enums\CourseStatus;
use App\Enums\LessonType;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearnExperienceTest extends TestCase
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

    public function test_enrolled_student_can_open_lesson_player(): void
    {
        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourseWithLesson();
        $lesson = $course->lessons()->first();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('learn.show', ['slug' => $course->slug, 'lesson' => $lesson->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Student/Learn/Show')
                ->where('lesson.id', $lesson->id)
                ->where('is_enrolled', true));
    }

    public function test_student_can_save_article_progress_and_complete_without_quiz(): void
    {
        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourseWithLesson();
        $lesson = $course->lessons()->first();
        $lesson->update([
            'type' => LessonType::Article,
            'require_quiz_to_complete' => false,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        $this->actingAs($student)
            ->patchJson(route('learn.lessons.progress', $lesson), [
                'mark_content_complete' => true,
            ])
            ->assertOk()
            ->assertJsonPath('lesson_complete', true);

        $this->assertDatabaseHas('lesson_progress', [
            'enrollment_id' => $enrollment->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        $this->assertNotNull($progress->completed_at);
    }

    public function test_student_must_pass_quiz_to_complete_lesson(): void
    {
        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourseWithLesson();
        $lesson = $course->lessons()->first();
        $lesson->update(['require_quiz_to_complete' => true]);

        $question = QuizQuestion::create([
            'lesson_id' => $lesson->id,
            'prompt' => 'Pick one',
            'sort_order' => 0,
        ]);

        $correct = QuizOption::create([
            'quiz_question_id' => $question->id,
            'label' => 'Correct',
            'is_correct' => true,
        ]);

        QuizOption::create([
            'quiz_question_id' => $question->id,
            'label' => 'Wrong',
            'is_correct' => false,
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        LessonProgress::create([
            'enrollment_id' => $enrollment->id,
            'lesson_id' => $lesson->id,
            'content_completed_at' => now(),
            'watched_percent' => 0,
        ]);

        $this->actingAs($student)
            ->postJson(route('learn.lessons.quiz.submit', $lesson), [
                'answers' => [
                    ['question_id' => $question->id, 'option_id' => $correct->id],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('passed', true)
            ->assertJsonPath('lesson_complete', true);
    }

    public function test_continue_redirects_to_first_incomplete_lesson(): void
    {
        $student = User::where('email', 'student@elearning.local')->first();
        $course = $this->publishedCourseWithLesson();
        $lessons = $course->lessons()->orderBy('id')->get();
        $first = $lessons->first();
        $second = $lessons->skip(1)->first();

        if ($second === null) {
            $section = Section::create([
                'course_id' => $course->id,
                'title' => 'More',
                'sort_order' => 1,
            ]);
            $second = $section->lessons()->create([
                'title' => 'Second lesson',
                'type' => LessonType::Article,
                'sort_order' => 0,
            ]);
        }

        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        LessonProgress::create([
            'enrollment_id' => $enrollment->id,
            'lesson_id' => $first->id,
            'content_completed_at' => now(),
            'completed_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('learn.continue', $course->slug))
            ->assertRedirect(route('learn.show', ['slug' => $course->slug, 'lesson' => $second->id]));
    }

    protected function publishedCourseWithLesson(): Course
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();
        $category = Category::first();

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'slug' => 'learn-test-course',
            'title' => 'Learn Test',
            'summary' => '<p>Test</p>',
            'status' => CourseStatus::Published,
        ]);

        $section = Section::create([
            'course_id' => $course->id,
            'title' => 'Section',
            'sort_order' => 0,
        ]);

        $section->lessons()->create([
            'title' => 'Lesson A',
            'type' => LessonType::Article,
            'content' => '<p>Body</p>',
            'sort_order' => 0,
        ]);

        $section->lessons()->create([
            'title' => 'Lesson B',
            'type' => LessonType::Article,
            'content' => '<p>Body B</p>',
            'sort_order' => 1,
        ]);

        return $course->fresh();
    }
}
