<?php

namespace App\Http\Requests\Teacher;

use App\Models\Assignment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A teacher records a student's submission (e.g. a scanned hard copy handed in class).
 */
class RecordSubmissionRequest extends FormRequest
{
    /**
     * Only the assignment owner may record submissions.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('assignment'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Assignment $assignment */
        $assignment = $this->route('assignment');

        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where('school_class_id', $assignment->school_class_id),
                Rule::unique('assignment_submissions', 'student_id')->where('assignment_id', $assignment->id),
            ],
            'submitted_at' => ['required', 'date', 'before_or_equal:now'],
            'file' => ['nullable', 'file', 'mimes:'.implode(',', config('schoolhub.uploads.mimes')), 'max:'.config('schoolhub.uploads.max_kb')],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.exists' => __('Please choose a student from this class.'),
            'student_id.unique' => __('This student already has a submission recorded.'),
            'submitted_at.before_or_equal' => __('The submission date cannot be in the future.'),
            'file.mimes' => __('This file type is not allowed. Please upload a PDF, Word, PowerPoint, Excel, JPG or PNG file.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['student_id' => __('student'), 'submitted_at' => __('submission date')];
    }
}
