<?php

namespace App\Http\Requests\Admin;

use App\Enums\LessonType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lesson = $this->route('lesson');

        return $lesson !== null && $this->user()?->can('update', $lesson) === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('duration_seconds') === '' || $this->input('duration_seconds') === null) {
            $this->merge(['duration_seconds' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(LessonType::class)],
            'summary' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'require_quiz_to_complete' => ['sometimes', 'boolean'],
            'quiz_pass_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_preview' => ['sometimes', 'boolean'],
            'video' => [
                'nullable',
                'file',
                'mimetypes:video/mp4,video/webm',
                'max:'.config('media.lesson_video_max_kilobytes', 204800),
            ],
            'quiz_questions' => ['nullable', 'array'],
            'quiz_questions.*.id' => ['nullable', 'integer'],
            'quiz_questions.*.prompt' => ['required_with:quiz_questions', 'string'],
            'quiz_questions.*.options' => ['required_with:quiz_questions', 'array', 'min:2'],
            'quiz_questions.*.options.*.id' => ['nullable', 'integer'],
            'quiz_questions.*.options.*.label' => ['required_with:quiz_questions', 'string', 'max:500'],
            'quiz_questions.*.options.*.is_correct' => ['sometimes', 'boolean'],
        ];
    }
}
