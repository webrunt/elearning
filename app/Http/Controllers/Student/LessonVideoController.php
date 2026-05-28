<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonVideoController extends Controller
{
    public function show(Request $request, Lesson $lesson): StreamedResponse
    {
        $this->authorize('learn', $lesson);

        if ($lesson->video_path === null || $lesson->video_disk === null) {
            abort(404);
        }

        $disk = Storage::disk($lesson->video_disk);

        if (! $disk->exists($lesson->video_path)) {
            abort(404);
        }

        return $disk->response($lesson->video_path);
    }
}
