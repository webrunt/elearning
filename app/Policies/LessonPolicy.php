<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use App\Services\LessonAccess;

class LessonPolicy
{
    public function learn(?User $user, Lesson $lesson): bool
    {
        return app(LessonAccess::class)->canView($user, $lesson);
    }

    public function trackProgress(User $user, Lesson $lesson): bool
    {
        return app(LessonAccess::class)->canTrackProgress($user, $lesson);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        $lesson->loadMissing('section.course');

        return (new CoursePolicy)->update($user, $lesson->section->course);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        $lesson->loadMissing('section.course');

        return (new CoursePolicy)->delete($user, $lesson->section->course);
    }
}
