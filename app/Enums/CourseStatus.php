<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Draft->value => 'Draft',
            self::PendingReview->value => 'Pending review',
            self::Published->value => 'Published',
            self::Archived->value => 'Archived',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labelsForStaff(bool $isAdmin): array
    {
        if ($isAdmin) {
            return self::labels();
        }

        return [
            self::Draft->value => 'Draft',
        ];
    }
}
