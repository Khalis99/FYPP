<?php

namespace App\Http\Requests\Teacher;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\Rph\RphGenerationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class GenerateRphRequest extends TeacherFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'language' => ['required', Rule::in(array_keys(RphGenerationService::LANGUAGES))],
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
            'learning_objectives' => ['required', 'string', 'max:2000'],
            'learning_standards' => ['nullable', 'string', 'max:2000'],
            'teaching_aids' => ['nullable', 'string', 'max:1000'],
            'previous_knowledge' => ['nullable', 'string', 'max:1000'],
            'teaching_method' => ['nullable', 'string', 'max:500'],
            'additional_instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return parent::attributes() + [
            'lesson_date' => 'date',
            'start_time' => 'start time',
            'duration_minutes' => 'duration',
            'student_count' => 'number of students',
        ];
    }

    /**
     * The validated input with IDs replaced by readable names, as sent to the AI.
     * Header-only fields (week, start time) are not needed by the AI.
     *
     * @return array<string, string|int|null>
     */
    public function forAi(): array
    {
        $data = $this->validated();

        return [
            'language' => RphGenerationService::LANGUAGES[$data['language']],
            'subject' => Subject::findOrFail($data['subject_id'])->name,
            'class' => SchoolClass::findOrFail($data['school_class_id'])->name,
            'theme' => $data['theme'] ?? null,
            'unit' => $data['unit'] ?? null,
            'topic' => $data['topic'],
            'lesson_date' => $data['lesson_date'],
            'duration_minutes' => (int) $data['duration_minutes'],
            'student_count' => isset($data['student_count']) ? (int) $data['student_count'] : null,
            'learning_objectives' => $data['learning_objectives'],
            'learning_standards' => $data['learning_standards'] ?? null,
            'teaching_aids' => $data['teaching_aids'] ?? null,
            'previous_knowledge' => $data['previous_knowledge'] ?? null,
            'teaching_method' => $data['teaching_method'] ?? null,
            'additional_instructions' => $data['additional_instructions'] ?? null,
        ];
    }
}
