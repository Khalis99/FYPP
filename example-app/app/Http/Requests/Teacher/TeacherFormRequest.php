<?php

namespace App\Http\Requests\Teacher;

use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Shared behaviour for teacher forms: upload rules and a check that the
 * teacher actually teaches the selected subject in the selected class.
 */
abstract class TeacherFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->teacher !== null;
    }

    public function teacher(): Teacher
    {
        return $this->user()->teacher;
    }

    /**
     * @return list<string>
     */
    protected function fileRules(bool $required): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:'.implode(',', config('schoolhub.uploads.mimes')),
            'max:'.config('schoolhub.uploads.max_kb'),
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['subject_id', 'school_class_id'])) {
                    return;
                }

                $subjectId = (int) $this->input('subject_id');
                $classId = $this->input('school_class_id');

                $allowed = $classId
                    ? $this->teacher()->teachesSubjectInClass($subjectId, (int) $classId)
                    : $this->teacher()->teachingLoads()->where('subject_id', $subjectId)->exists();

                if (! $allowed) {
                    $validator->errors()->add('school_class_id', 'You are not assigned to teach this subject in the selected class.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.max' => 'The file is too large. The maximum size is '.(config('schoolhub.uploads.max_kb') / 1024).' MB.',
            'file.mimes' => 'This file type is not allowed. Please upload a PDF, Word, PowerPoint, Excel, JPG or PNG file.',
            'attachment.max' => 'The file is too large. The maximum size is '.(config('schoolhub.uploads.max_kb') / 1024).' MB.',
            'attachment.mimes' => 'This file type is not allowed. Please upload a PDF, Word, PowerPoint, Excel, JPG or PNG file.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'subject_id' => 'subject',
            'school_class_id' => 'class',
        ];
    }
}
