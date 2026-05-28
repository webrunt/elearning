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

        $htmlLesson = $sectionTwo->lessons()->create([
            'title' => 'HTML structure basics',
            'type' => LessonType::Article,
            'summary' => '<p>Semantic HTML elements and document structure.</p>',
            'content' => '<p>Learn semantic HTML and page structure. Use headings, paragraphs, and lists correctly.</p>',
            'sort_order' => 0,
            'require_quiz_to_complete' => true,
            'quiz_pass_percent' => 70,
        ]);

        $questionOne = $htmlLesson->quizQuestions()->create([
            'prompt' => 'Which element is best for the main page heading?',
            'sort_order' => 0,
        ]);
        $questionOne->options()->createMany([
            ['label' => '<h1>', 'is_correct' => true],
            ['label' => '<div>', 'is_correct' => false],
            ['label' => '<span>', 'is_correct' => false],
        ]);

        $questionTwo = $htmlLesson->quizQuestions()->create([
            'prompt' => 'What does semantic HTML improve?',
            'sort_order' => 1,
        ]);
        $questionTwo->options()->createMany([
            ['label' => 'Accessibility and meaning', 'is_correct' => true],
            ['label' => 'Faster video streaming', 'is_correct' => false],
            ['label' => 'Database queries', 'is_correct' => false],
        ]);

        $questionThree = $htmlLesson->quizQuestions()->create([
            'prompt' => 'Which tag defines a paragraph?',
            'sort_order' => 2,
        ]);
        $questionThree->options()->createMany([
            ['label' => '<p>', 'is_correct' => true],
            ['label' => '<section>', 'is_correct' => false],
            ['label' => '<article>', 'is_correct' => false],
        ]);
    }
}
