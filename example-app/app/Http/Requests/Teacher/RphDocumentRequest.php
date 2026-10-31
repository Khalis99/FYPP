<?php

namespace App\Http\Requests\Teacher;

use App\Services\Rph\RphContent;
use Illuminate\Contracts\Validation\ValidationRule;

class RphDocumentRequest extends TeacherFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'topic' => ['required', 'string', 'max:255'],
            'theme' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'lesson_date' => ['required', 'date'],
            'week' => ['nullable', 'integer', 'min:1', 'max:53'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:300'],
            'student_count' => ['nullable', 'integer', 'min:1', 'max:100'],
            'content' => ['required', 'array'],
            'content.learning_objectives' => ['required', 'string', 'max:5000'],
        ];

        foreach (array_keys(RphContent::SECTIONS) as $section) {
            $rules["content.{$section}"] ??= ['nullable', 'string', 'max:5000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = parent::attributes() + [
            'lesson_date' => __('date'),
            'start_time' => __('start time'),
            'duration_minutes' => __('duration'),
            'student_count' => __('number of students'),
        ];

        foreach (RphContent::SECTIONS as $key => [$label]) {
            $attributes["content.{$key}"] = strtolower($label);
        }

        return $attributes;
    }

    /**
     * Attributes ready for RphDocument::create()/update().
     *
     * @return array<string, mixed>
     */
    public function documentData(): array
    {
        $data = $this->safe()->except('content');
        $data['content'] = RphContent::normalize($this->validated('content'));

        return $data;
    }
}
