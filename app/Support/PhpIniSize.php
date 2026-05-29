<?php

namespace App\Support;

class PhpIniSize
{
    public static function toBytes(string|false|null $value): int
    {
        if ($value === false || $value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $metric = strtoupper(substr((string) $value, -1));
        $number = (int) $value;

        return match ($metric) {
            'G' => $number * 1073741824,
            'M' => $number * 1048576,
            'K' => $number * 1024,
            default => $number,
        };
    }

    public static function postMaxBytes(): int
    {
        return self::toBytes(ini_get('post_max_size'));
    }

    public static function uploadMaxBytes(): int
    {
        return self::toBytes(ini_get('upload_max_filesize'));
    }

    /**
     * @return array{post_max_mb: float, upload_max_mb: float, app_max_mb: float, effective_max_mb: float}
     */
    public static function lessonVideoLimits(): array
    {
        $postMax = self::postMaxBytes();
        $uploadMax = self::uploadMaxBytes();
        $appMaxBytes = (int) config('media.lesson_video_max_kilobytes', 204800) * 1024;

        $effective = min(
            array_filter([$postMax, $uploadMax, $appMaxBytes], static fn (int $bytes) => $bytes > 0) ?: [$appMaxBytes]
        );

        return [
            'post_max_mb' => round($postMax / 1048576, 1),
            'upload_max_mb' => round($uploadMax / 1048576, 1),
            'app_max_mb' => round($appMaxBytes / 1048576, 1),
            'effective_max_mb' => round($effective / 1048576, 1),
        ];
    }
}
