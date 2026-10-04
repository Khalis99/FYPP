<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
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
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'instructions' => fake()->sentence(),
            'due_at' => now()->addDays(fake()->numberBetween(2, 14))->setTime(23, 59),
        ];
    }

    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => now()->subDays(fake()->numberBetween(1, 10))->setTime(23, 59),
        ]);
    }
}
