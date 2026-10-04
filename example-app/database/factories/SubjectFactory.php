<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Mathematics', 'Science', 'English', 'Bahasa Melayu', 'History',
            'Geography', 'Design & Technology', 'Physical Education', 'Art', 'Music',
        ]);

        return [
            'name' => $name,
            'code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3)).fake()->unique()->numberBetween(10, 99),
        ];
    }
}
