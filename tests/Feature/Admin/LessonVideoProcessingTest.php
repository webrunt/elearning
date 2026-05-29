<?php

namespace Tests\Feature\Admin;

use App\Enums\LessonType;
use App\Enums\VideoProcessingStatus;
use App\Jobs\ProcessLessonVideo;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;
use App\Services\LessonVideoProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonVideoProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config(['domains.admin' => 'admin.elearning.local']);
        Storage::fake('public');
    }

    public function test_video_upload_dispatches_processing_job(): void
    {
        Queue::fake();

        $lesson = $this->createVideoLessonForInstructor();

        $response = $this->actingAs(User::where('email', 'instructor@elearning.local')->first())
            ->put('http://admin.elearning.local/lessons/'.$lesson->id, [
                'title' => $lesson->title,
                'type' => LessonType::Video->value,
                'summary' => '',
                'require_quiz_to_complete' => false,
                'quiz_pass_percent' => 70,
                'is_preview' => false,
                'quiz_questions' => [],
                'video' => UploadedFile::fake()->create('lesson.mp4', 100, 'video/mp4'),
            ]);

        $response->assertRedirect(route('admin.lessons.edit', $lesson, absolute: false));

        $lesson->refresh();

        $this->assertSame(VideoProcessingStatus::Pending, $lesson->video_processing_status);
        $this->assertNotNull($lesson->video_path);

        Queue::assertPushed(ProcessLessonVideo::class, function (ProcessLessonVideo $job) use ($lesson) {
            return $job->lessonId === $lesson->id;
        });
    }

    public function test_process_lesson_video_job_updates_duration_when_processor_succeeds(): void
    {
        $lesson = $this->createVideoLessonWithStoredVideo();

        $audioPath = 'courses/'.$lesson->section->course_id.'/audio/'.$lesson->id.'.mp3';

        $processor = $this->createMock(LessonVideoProcessor::class);
        $processor->method('isAvailable')->willReturn(true);
        $processor->method('process')->willReturn([
            'duration_seconds' => 600,
            'audio_path' => $audioPath,
            'audio_disk' => 'public',
        ]);

        $this->app->instance(LessonVideoProcessor::class, $processor);

        $lesson->update([
            'video_processing_status' => VideoProcessingStatus::Pending,
        ]);

        ProcessLessonVideo::dispatchSync($lesson->id);

        $lesson->refresh();

        $this->assertSame(VideoProcessingStatus::Completed, $lesson->video_processing_status);
        $this->assertSame(600, $lesson->duration_seconds);
        $this->assertSame($audioPath, $lesson->audio_path);
        $this->assertNotNull($lesson->video_processed_at);
    }

    public function test_process_lesson_video_job_skips_when_ffmpeg_unavailable(): void
    {
        $lesson = $this->createVideoLessonWithStoredVideo();

        $processor = $this->createMock(LessonVideoProcessor::class);
        $processor->method('isAvailable')->willReturn(false);

        $this->app->instance(LessonVideoProcessor::class, $processor);

        ProcessLessonVideo::dispatchSync($lesson->id);

        $lesson->refresh();

        $this->assertSame(VideoProcessingStatus::Skipped, $lesson->video_processing_status);
    }

    protected function createVideoLessonForInstructor(): Lesson
    {
        $instructor = User::where('email', 'instructor@elearning.local')->first();

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'slug' => 'ffmpeg-test-course',
            'title' => 'FFmpeg test course',
            'status' => 'draft',
        ]);

        $section = Section::create([
            'course_id' => $course->id,
            'title' => 'Section 1',
            'sort_order' => 0,
        ]);

        return $section->lessons()->create([
            'title' => 'Video lesson',
            'type' => LessonType::Video,
            'sort_order' => 0,
        ]);
    }

    protected function createVideoLessonWithStoredVideo(): Lesson
    {
        $lesson = $this->createVideoLessonForInstructor();
        $lesson->load('section.course');

        $videoPath = 'courses/'.$lesson->section->course_id.'/videos/test.mp4';
        Storage::disk('public')->put($videoPath, 'fake-video-content');

        $lesson->update([
            'video_path' => $videoPath,
            'video_disk' => 'public',
        ]);

        return $lesson->fresh();
    }
}
