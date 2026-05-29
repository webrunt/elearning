<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;

class CourseReviewPolicy
{
    public function moderate(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
    }

    public function create(User $user, Course $course): bool
    {
        return $user->hasRole(User::ROLE_STUDENT)
            && $user->isEnrolledIn($course);
    }

    public function update(User $user, CourseReview $review): bool
    {
        return (int) $review->user_id === (int) $user->id
            && $review->isPending();
    }
}
