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
        $names = [
            'Mathematics', 'Science', 'English', 'Bahasa Melayu', 'History',
            'Geography', 'Design & Technology', 'Physical Education', 'Art', 'Music',
        ];

        // Subject names are unique, so skip the ones a test has already created by hand.
        $available = array_values(array_diff($names, Subject::pluck('name')->all()));

        $name = fake()->unique()->randomElement($available ?: $names);

        return [
            'name' => $name,
            'code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3)).fake()->unique()->numberBetween(10, 99),
        ];
    }
}
