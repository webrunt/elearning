<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');

        return $target !== null && $this->user()?->can('update', $target) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::in([
                    User::ROLE_STUDENT,
                    User::ROLE_INSTRUCTOR,
                    User::ROLE_ADMIN,
                    User::ROLE_SUPER_ADMIN,
                ]),
            ],
        ];
    }
}
