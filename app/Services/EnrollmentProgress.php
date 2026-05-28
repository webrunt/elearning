<?php

namespace App\Services;

use App\Models\Enrollment;

class EnrollmentProgress
{
    public function percent(Enrollment $enrollment): int
    {
        $enrollment->loadMissing('course');

        $totalLessons = $enrollment->course->lessons()->count();

        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = $enrollment->lessonProgress()
            ->whereNotNull('completed_at')
            ->count();

        return (int) round(($completedLessons / $totalLessons) * 100);
    }

    public function completedLessonsCount(Enrollment $enrollment): int
    {
        return $enrollment->lessonProgress()
            ->whereNotNull('completed_at')
            ->count();
    }

    public function totalLessonsCount(Enrollment $enrollment): int
    {
        $enrollment->loadMissing('course');

        return $enrollment->course->lessons()->count();
    }
}
