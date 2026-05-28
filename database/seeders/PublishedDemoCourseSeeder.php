<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Enums\LessonType;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class PublishedDemoCourseSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();

        if ($instructor === null) {
            return;
        }

        $category = Category::where('slug', 'development')->first();

        $course = Course::firstOrCreate(
            ['slug' => 'intro-to-web-development'],
            [
                'instructor_id' => $instructor->id,
                'category_id' => $category?->id,
                'title' => 'Introduction to Web Development',
                'summary' => '<p>Learn the <strong>fundamentals of HTML, CSS, and JavaScript</strong> in a structured, hands-on course.</p>',
                'status' => CourseStatus::Published,
                'price' => null,
            ]
        );

        $course->update([
            'status' => CourseStatus::Published,
            'price' => null,
        ]);

        if ($course->sections()->count() > 0) {
            return;
        }

        $sectionOne = $course->sections()->create([
            'title' => 'Getting started',
            'sort_order' => 0,
        ]);

        $sectionOne->lessons()->create([
            'title' => 'Welcome to the course',
            'type' => LessonType::Article,
            'summary' => 'What you will learn and how the course is organized.',
            'content' => '<p>Welcome! This demo course is ready for free enrollment.</p>',
            'sort_order' => 0,
            'is_preview' => true,
        ]);

        $sectionOne->lessons()->create([
            'title' => 'Setting up your environment',
            'type' => LessonType::Video,
            'summary' => 'Install the tools you need for web development.',
            'sort_order' => 1,
            'require_quiz_to_complete' => false,
        ]);

        $sectionTwo = $course->sections()->create([
            'title' => 'Core concepts',
            'sort_order' => 1,
        ]);

        $sectionTwo->lessons()->create([
            'title' => 'HTML structure basics',
            'type' => LessonType::Article,
            'content' => '<p>Learn semantic HTML and page structure.</p>',
            'sort_order' => 0,
        ]);
    }
}
