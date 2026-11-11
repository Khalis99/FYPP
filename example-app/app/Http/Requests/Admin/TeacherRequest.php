<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Account fields (including the six-digit staff ID) come from UserRequest; this adds the teacher profile.
 */
class TeacherRequest extends UserRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'department' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function editedUserId(): ?int
    {
        return $this->route('teacher')?->user_id;
    }
}
