<?php

namespace App\Services;

use App\Enums\CourseStatus;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;

class LessonAccess
{
    public function canView(?User $user, Lesson $lesson): bool
    {
        $lesson->loadMissing('section.course');
        $course = $lesson->section->course;

        if ($course->status !== CourseStatus::Published) {
            return false;
        }

        if ($lesson->is_preview) {
            return true;
        }

        if ($user === null || ! $user->isStudent()) {
            return false;
        }

        return $user->isEnrolledIn($course);
    }

    public function canTrackProgress(?User $user, Lesson $lesson): bool
    {
        if ($user === null || ! $user->isStudent()) {
            return false;
        }

        $lesson->loadMissing('section.course');

        if ($lesson->is_preview) {
            return $user->isEnrolledIn($lesson->section->course);
        }

        return $this->canView($user, $lesson);
    }

    public function enrollmentFor(?User $user, Lesson $lesson): ?Enrollment
    {
        if ($user === null) {
            return null;
        }

        $lesson->loadMissing('section.course');

        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $lesson->section->course_id)
            ->first();
    }
}
