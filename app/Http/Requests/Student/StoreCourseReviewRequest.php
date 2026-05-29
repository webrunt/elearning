<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = \App\Models\Course::published()
            ->where('slug', $this->route('slug'))
            ->first();

        return $course !== null
            && $this->user()?->can('create', [\App\Models\CourseReview::class, $course]) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
