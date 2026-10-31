<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * When creating, a subject and teacher can optionally be assigned straight away.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('school_classes')->where('academic_year', $this->input('academic_year'))->ignore($this->route('schoolClass')?->id),
            ],
            'form_level' => ['required', 'integer', 'between:1,6'],
            'academic_year' => ['required', 'string', 'regex:/^\d{4}$/'],
            'subject_id' => ['nullable', 'required_with:teacher_id', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'required_with:subject_id', 'integer', 'exists:teachers,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => __('A class with this name already exists for the selected academic year.'),
            'academic_year.regex' => __('The academic year must be a 4-digit year, for example 2026.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['form_level' => __('form'), 'subject_id' => __('subject'), 'teacher_id' => __('teacher')];
    }
}
