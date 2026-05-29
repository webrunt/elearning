<?php

namespace App\Jobs;

use App\Enums\VideoProcessingStatus;
use App\Models\Lesson;
use App\Services\LessonVideoProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessLessonVideo implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        public int $lessonId,
    ) {}

    public function handle(LessonVideoProcessor $processor): void
    {
        $lesson = Lesson::query()->find($this->lessonId);

        if ($lesson === null || $lesson->video_path === null || $lesson->video_disk === null) {
            return;
        }

        $lesson->update([
            'video_processing_status' => VideoProcessingStatus::Processing,
            'video_processing_error' => null,
        ]);

        if (! $processor->isAvailable()) {
            $lesson->update([
                'video_processing_status' => VideoProcessingStatus::Skipped,
                'video_processing_error' => null,
                'video_processed_at' => now(),
            ]);

            return;
        }

        try {
            $result = $processor->process($lesson);

            $lesson->update([
                'duration_seconds' => $result['duration_seconds'],
                'audio_path' => $result['audio_path'],
                'audio_disk' => $result['audio_disk'],
                'video_processing_status' => VideoProcessingStatus::Completed,
                'video_processing_error' => null,
                'video_processed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $lesson->update([
                'video_processing_status' => VideoProcessingStatus::Failed,
                'video_processing_error' => $exception->getMessage(),
                'video_processed_at' => now(),
            ]);
        }
    }
}
