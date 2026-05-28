<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course !== null && $this->user()?->can('review', $course) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rejection_feedback' => ['required', 'string', 'max:5000'],
            'review_summary' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
