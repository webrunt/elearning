<?php

namespace App\Http\Requests\Admin;

use App\Enums\CourseStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course !== null && $this->user()?->can('update', $course) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_id' => $this->input('category_id') ?: null,
            'instructor_id' => $this->input('instructor_id') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'instructor_id' => ['nullable', 'exists:users,id'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
        ];

        if ($this->user()?->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            $rules['status'] = ['required', Rule::enum(CourseStatus::class)];
        }

        return $rules;
    }
}
