<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Support\Collection;

class CourseCurriculum
{
    /**
     * @return Collection<int, Lesson>
     */
    public function orderedLessons(Course $course): Collection
    {
        $course->load([
            'sections.lessons' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $lessons = collect();

        foreach ($course->sections as $section) {
            foreach ($section->lessons as $lesson) {
                $lessons->push($lesson);
            }
        }

        return $lessons;
    }

    public function firstLesson(Course $course): ?Lesson
    {
        return $this->orderedLessons($course)->first();
    }

    public function nextLesson(Course $course, Lesson $current): ?Lesson
    {
        $lessons = $this->orderedLessons($course);
        $index = $lessons->search(fn (Lesson $lesson) => $lesson->id === $current->id);

        if ($index === false) {
            return null;
        }

        return $lessons->get($index + 1);
    }

    public function previousLesson(Course $course, Lesson $current): ?Lesson
    {
        $lessons = $this->orderedLessons($course);
        $index = $lessons->search(fn (Lesson $lesson) => $lesson->id === $current->id);

        if ($index === false || $index === 0) {
            return null;
        }

        return $lessons->get($index - 1);
    }

    public function firstIncompleteLesson(Course $course, Enrollment $enrollment): ?Lesson
    {
        $completedIds = $enrollment->lessonProgress()
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->all();

        foreach ($this->orderedLessons($course) as $lesson) {
            if (! in_array($lesson->id, $completedIds, true)) {
                return $lesson;
            }
        }

        return null;
    }
}
