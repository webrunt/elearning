<?php

namespace App\Http\Requests\Admin;

use App\Enums\LessonType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $section = $this->route('section');
        if ($section === null) {
            return false;
        }
        $section->loadMissing('course');

        return $this->user()?->can('update', $section->course) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(LessonType::class)],
        ];
    }
}
