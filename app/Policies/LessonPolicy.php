<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
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
