<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lesson = $this->route('lesson');

        return $lesson !== null && $this->user()?->can('trackProgress', $lesson) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'last_position_seconds' => ['nullable', 'integer', 'min:0'],
            'watched_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'mark_content_complete' => ['sometimes', 'boolean'],
        ];
    }
}
