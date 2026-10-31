<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Contracts\Validation\ValidationRule;

class AssignmentRequest extends TeacherFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            // New assignments must be due in the future; existing ones may keep a past date.
            'due_at' => $this->isMethod('post') ? ['required', 'date', 'after:now'] : ['required', 'date'],
            'attachment' => $this->fileRules(required: false),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + [
            'due_at.after' => __('The due date must be in the future.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return parent::attributes() + ['due_at' => __('due date')];
    }
}
