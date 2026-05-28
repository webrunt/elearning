<?php

namespace App\Enums;

enum LessonType: string
{
    case Video = 'video';
    case Article = 'article';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Video->value => 'Video',
            self::Article->value => 'Article',
        ];
    }
}
