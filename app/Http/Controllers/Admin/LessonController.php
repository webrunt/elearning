<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LessonType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function store(StoreLessonRequest $request, Section $section): RedirectResponse
    {
        $maxOrder = $section->lessons()->max('sort_order');

        $lesson = $section->lessons()->create([
            'title' => $request->string('title')->toString(),
            'type' => $request->input('type'),
            'sort_order' => $maxOrder !== null ? ((int) $maxOrder) + 1 : 0,
        ]);

        return redirect()
            ->route('admin.lessons.edit', $lesson)
            ->with('success', 'Lesson created. Add content and quiz questions.');
    }

    public function edit(Lesson $lesson): Response
    {
        $this->authorize('update', $lesson);

        $lesson->load([
            'section.course',
            'quizQuestions.options',
        ]);

        $course = $lesson->section->course;

        return Inertia::render('Admin/Lessons/Edit', [
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'type' => $lesson->type->value,
                'summary' => $lesson->summary,
                'content' => $lesson->content,
                'duration_seconds' => $lesson->duration_seconds,
                'require_quiz_to_complete' => $lesson->require_quiz_to_complete,
                'quiz_pass_percent' => $lesson->quiz_pass_percent,
                'is_preview' => $lesson->is_preview,
                'video_url' => $lesson->video_path && $lesson->video_disk
                    ? Storage::disk($lesson->video_disk)->url($lesson->video_path)
                    : null,
                'quiz_questions' => $lesson->quizQuestions->map(fn (QuizQuestion $question) => [
                    'id' => $question->id,
                    'prompt' => $question->prompt,
                    'options' => $question->options->map(fn (QuizOption $option) => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'is_correct' => $option->is_correct,
                    ]),
                ]),
            ],
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
            ],
            'section' => [
                'id' => $lesson->section->id,
                'title' => $lesson->section->title,
            ],
            'lesson_types' => LessonType::labels(),
        ]);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $lesson->loadMissing('section.course');
        $courseId = $lesson->section->course_id;

        $data = [
            'title' => $request->string('title')->toString(),
            'type' => $request->input('type'),
            'summary' => $request->input('summary'),
            'content' => $request->input('content'),
            'duration_seconds' => $request->input('duration_seconds'),
            'require_quiz_to_complete' => $request->boolean('require_quiz_to_complete'),
            'quiz_pass_percent' => (int) $request->input('quiz_pass_percent', 70),
            'is_preview' => $request->boolean('is_preview'),
        ];

        if ($request->hasFile('video')) {
            if ($lesson->video_path !== null && $lesson->video_disk !== null) {
                Storage::disk($lesson->video_disk)->delete($lesson->video_path);
            }

            $path = $request->file('video')->store('courses/'.$courseId.'/videos', 'public');
            $data['video_path'] = $path;
            $data['video_disk'] = 'public';
        }

        DB::transaction(function () use ($lesson, $data, $request) {
            $lesson->update($data);
            $this->syncQuizQuestions($lesson, $request->input('quiz_questions', []));
        });

        return redirect()
            ->route('admin.courses.edit', $lesson->section->course_id)
            ->with('success', 'Lesson saved.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->loadMissing('section');
        $courseId = $lesson->section->course_id;

        if ($lesson->video_path !== null && $lesson->video_disk !== null) {
            Storage::disk($lesson->video_disk)->delete($lesson->video_path);
        }

        $lesson->delete();

        return redirect()
            ->route('admin.courses.edit', $courseId)
            ->with('success', 'Lesson deleted.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $questionsInput
     */
    protected function syncQuizQuestions(Lesson $lesson, array $questionsInput): void
    {
        $keptQuestionIds = [];

        foreach ($questionsInput as $index => $questionData) {
            $prompt = $questionData['prompt'] ?? '';
            if ($prompt === '') {
                continue;
            }

            $questionId = $questionData['id'] ?? null;
            $question = null;

            if ($questionId !== null) {
                $question = QuizQuestion::where('lesson_id', $lesson->id)->where('id', $questionId)->first();
            }

            if ($question === null) {
                $question = $lesson->quizQuestions()->create([
                    'prompt' => $prompt,
                    'sort_order' => $index,
                ]);
            } else {
                $question->update([
                    'prompt' => $prompt,
                    'sort_order' => $index,
                ]);
            }

            $keptQuestionIds[] = $question->id;
            $this->syncQuizOptions($question, $questionData['options'] ?? []);
        }

        $lesson->quizQuestions()->whereNotIn('id', $keptQuestionIds)->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $optionsInput
     */
    protected function syncQuizOptions(QuizQuestion $question, array $optionsInput): void
    {
        $keptOptionIds = [];

        foreach ($optionsInput as $optionData) {
            $label = $optionData['label'] ?? '';
            if ($label === '') {
                continue;
            }

            $optionId = $optionData['id'] ?? null;
            $option = null;

            if ($optionId !== null) {
                $option = QuizOption::where('quiz_question_id', $question->id)->where('id', $optionId)->first();
            }

            if ($option === null) {
                $option = $question->options()->create([
                    'label' => $label,
                    'is_correct' => (bool) ($optionData['is_correct'] ?? false),
                ]);
            } else {
                $option->update([
                    'label' => $label,
                    'is_correct' => (bool) ($optionData['is_correct'] ?? false),
                ]);
            }

            $keptOptionIds[] = $option->id;
        }

        $question->options()->whereNotIn('id', $keptOptionIds)->delete();
    }
}
