<?php

namespace App\Http\Requests\Teacher;

use App\Enums\MaterialType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class TeachingMaterialRequest extends TeacherFormRequest
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
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'topic' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::enum(MaterialType::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => $this->fileRules(required: $this->isMethod('post')),
        ];
    }
}
