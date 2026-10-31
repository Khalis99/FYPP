<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class TeacherRequest extends UserRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'teacher_code' => ['required', 'string', 'max:20', Rule::unique('teachers')->ignore($this->route('teacher')?->id)],
            'department' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['teacher_code' => __('teacher ID')];
    }

    protected function editedUserId(): ?int
    {
        return $this->route('teacher')?->user_id;
    }
}
