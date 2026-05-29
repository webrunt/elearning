<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FFmpeg / FFprobe binaries
    |--------------------------------------------------------------------------
    |
    | Leave null to use binaries on the system PATH. Set full paths when
    | ffmpeg is not globally available (common on shared hosting).
    |
    */

    'ffmpeg.binaries' => env('FFMPEG_BINARIES'),

    'ffprobe.binaries' => env('FFPROBE_BINARIES'),

    'timeout' => (int) env('FFMPEG_TIMEOUT', 3600),

    'temporary_directory' => storage_path('app/ffmpeg-tmp'),

    /*
    |--------------------------------------------------------------------------
    | Extract MP3 audio track after upload (prep for Phase 3c STT)
    |--------------------------------------------------------------------------
    */

    'extract_audio' => (bool) env('FFMPEG_EXTRACT_AUDIO', true),

];
