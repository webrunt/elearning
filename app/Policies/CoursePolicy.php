<?php

namespace App\Policies;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Course $course): bool
    {
        return $this->canManage($user, $course);
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Course $course): bool
    {
        return $this->canManage($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->canManage($user, $course);
    }

    public function review(User $user, Course $course): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
    }

    public function submitForReview(User $user, Course $course): bool
    {
        return $user->hasRole(User::ROLE_INSTRUCTOR)
            && $course->isOwnedBy($user)
            && $course->status === CourseStatus::Draft;
    }

    protected function canManage(User $user, Course $course): bool
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            return true;
        }

        return $user->hasRole(User::ROLE_INSTRUCTOR) && $course->isOwnedBy($user);
    }
}
