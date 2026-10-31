<?php

namespace App\Http\Requests\Admin;

use App\Enums\Weekday;
use App\Models\ClassTeacher;
use App\Models\Timetable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TimetableRequest extends FormRequest
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
        return [
            'day' => ['required', Rule::enum(Weekday::class)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'room' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'start_time' => __('start time'),
            'end_time' => __('end time'),
            'subject_id' => __('subject'),
            'school_class_id' => __('class'),
            'teacher_id' => __('teacher'),
        ];
    }

    /**
     * Prevent obvious conflicts: the teacher, the class or the room being
     * booked twice at overlapping times on the same day.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $isAssigned = ClassTeacher::query()
                    ->where('teacher_id', $this->integer('teacher_id'))
                    ->where('school_class_id', $this->integer('school_class_id'))
                    ->where('subject_id', $this->integer('subject_id'))
                    ->exists();

                if (! $isAssigned) {
                    $validator->errors()->add('teacher_id', 'This teacher is not assigned to teach the selected subject in this class. Assign them on the class page first.');

                    return;
                }

                $overlapping = fn (): Builder => Timetable::query()
                    ->overlapping($this->integer('day'), $this->input('start_time'), $this->input('end_time'))
                    ->when($this->route('timetable'), fn (Builder $q, Timetable $current) => $q->whereKeyNot($current->id));

                if ($overlapping()->where('teacher_id', $this->integer('teacher_id'))->exists()) {
                    $validator->errors()->add('start_time', 'This teacher already has a class at this time.');
                }

                if ($overlapping()->where('school_class_id', $this->integer('school_class_id'))->exists()) {
                    $validator->errors()->add('start_time', 'This class already has a lesson at this time.');
                }

                if ($this->filled('room') && $overlapping()->where('room', $this->input('room'))->exists()) {
                    $validator->errors()->add('room', 'This room is already booked at this time.');
                }
            },
        ];
    }
}
