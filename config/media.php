<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lesson video upload limits
    |--------------------------------------------------------------------------
    |
    | Laravel "max" rule for files is in kilobytes. Large uploads need extra
    | PHP memory (multipart parsing + framework overhead), not just file size.
    |
    */

    'lesson_video_max_kilobytes' => (int) env('LESSON_VIDEO_MAX_KB', 204800),

    'lesson_video_memory_limit' => env('LESSON_VIDEO_MEMORY_LIMIT', '512M'),

];
