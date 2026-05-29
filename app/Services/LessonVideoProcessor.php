<?php

namespace App\Services;

use App\Models\Lesson;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class LessonVideoProcessor
{
    /**
     * @return array{duration_seconds: int, audio_path: ?string, audio_disk: ?string}
     */
    public function process(Lesson $lesson): array
    {
        $absolutePath = $this->absoluteVideoPath($lesson);

        if (! is_readable($absolutePath)) {
            throw new RuntimeException('Video file is not readable.');
        }

        $ffprobe = FFProbe::create($this->configuration(), Log::getLogger());

        if (! $ffprobe->isValid($absolutePath)) {
            throw new RuntimeException('Video file failed validation.');
        }

        $duration = (int) round((float) $ffprobe->format($absolutePath)->get('duration'));

        $audioPath = null;
        $audioDisk = null;

        if (config('ffmpeg.extract_audio')) {
            $this->deleteAudioFile($lesson);
            $audioPath = $this->extractAudio($lesson, $absolutePath);
            $audioDisk = $lesson->video_disk;
        }

        return [
            'duration_seconds' => max(0, $duration),
            'audio_path' => $audioPath,
            'audio_disk' => $audioDisk,
        ];
    }

    public function isAvailable(): bool
    {
        $configuration = $this->configuration();

        if ($configuration === []) {
            return $this->binaryExists('ffmpeg') && $this->binaryExists('ffprobe');
        }

        $ffmpeg = $configuration['ffmpeg.binaries'] ?? null;
        $ffprobe = $configuration['ffprobe.binaries'] ?? null;

        if ($ffmpeg !== null && $ffprobe !== null) {
            return is_executable($ffmpeg) && is_executable($ffprobe);
        }

        return $this->binaryExists('ffmpeg') && $this->binaryExists('ffprobe');
    }

    public function deleteAudioFile(Lesson $lesson): void
    {
        if ($lesson->audio_path === null || $lesson->audio_disk === null) {
            return;
        }

        Storage::disk($lesson->audio_disk)->delete($lesson->audio_path);
    }

    protected function absoluteVideoPath(Lesson $lesson): string
    {
        if ($lesson->video_path === null || $lesson->video_disk === null) {
            throw new RuntimeException('Lesson has no video file.');
        }

        return Storage::disk($lesson->video_disk)->path($lesson->video_path);
    }

    protected function extractAudio(Lesson $lesson, string $videoPath): string
    {
        $lesson->loadMissing('section.course');
        $courseId = $lesson->section->course_id;

        $this->ensureTemporaryDirectory();

        $ffmpeg = FFMpeg::create($this->configuration(), Log::getLogger());
        $video = $ffmpeg->open($videoPath);

        $tmpFile = config('ffmpeg.temporary_directory').'/'.uniqid('lesson_audio_', true).'.mp3';

        try {
            $video->save(new Mp3, $tmpFile);

            $storagePath = 'courses/'.$courseId.'/audio/'.$lesson->id.'.mp3';
            $disk = Storage::disk($lesson->video_disk);
            $stream = fopen($tmpFile, 'rb');

            if ($stream === false) {
                throw new RuntimeException('Could not read extracted audio file.');
            }

            try {
                $disk->writeStream($storagePath, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            return $storagePath;
        } finally {
            if (is_file($tmpFile)) {
                @unlink($tmpFile);
            }
        }
    }

    protected function ensureTemporaryDirectory(): void
    {
        $directory = config('ffmpeg.temporary_directory');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function configuration(): array
    {
        $configuration = [
            'ffmpeg.binaries' => config('ffmpeg.ffmpeg.binaries'),
            'ffprobe.binaries' => config('ffmpeg.ffprobe.binaries'),
            'timeout' => config('ffmpeg.timeout'),
            'temporary_directory' => config('ffmpeg.temporary_directory'),
        ];

        return array_filter(
            $configuration,
            static fn ($value) => $value !== null && $value !== ''
        );
    }

    protected function binaryExists(string $command): bool
    {
        try {
            $which = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
            $process = proc_open(
                [$which, $command],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes
            );

            if (! is_resource($process)) {
                return false;
            }

            foreach ($pipes as $pipe) {
                fclose($pipe);
            }

            return proc_close($process) === 0;
        } catch (Throwable) {
            return false;
        }
    }
}
