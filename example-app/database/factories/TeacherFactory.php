<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => UserRole::Teacher]),
            'department' => fake()->randomElement(['Science & Mathematics', 'Languages', 'Humanities', 'Technical & Vocational']),
        ];
    }

    /**
     * Assign this teacher to teach a subject in a class (creating either if not given).
     */
    public function teaching(?SchoolClass $schoolClass = null, ?Subject $subject = null): static
    {
        return $this->afterCreating(function (Teacher $teacher) use ($schoolClass, $subject): void {
            ClassTeacher::create([
                'teacher_id' => $teacher->id,
                'school_class_id' => ($schoolClass ?? SchoolClass::factory()->create())->id,
                'subject_id' => ($subject ?? Subject::factory()->create())->id,
            ]);
        });
    }
}
