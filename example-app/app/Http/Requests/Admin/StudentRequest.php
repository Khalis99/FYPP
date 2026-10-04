<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'student_code' => ['required', 'string', 'max:20', Rule::unique('students')->ignore($studentId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('students')->ignore($studentId)],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['student_code' => 'student ID', 'school_class_id' => 'class', 'is_active' => 'status'];
    }
}
