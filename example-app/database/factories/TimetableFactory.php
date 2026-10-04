<?php

namespace Database\Factories;

use App\Enums\Weekday;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timetable>
 */
class TimetableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'school_class_id' => SchoolClass::factory(),
            'day' => Weekday::Monday,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => 'Room '.fake()->numberBetween(101, 310),
        ];
    }
}
