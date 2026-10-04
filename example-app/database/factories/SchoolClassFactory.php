<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->numberBetween(1, 5);

        return [
            'name' => $level.' '.fake()->unique()->randomElement(['Amanah', 'Bestari', 'Cekal', 'Dedikasi', 'Elit', 'Fikrah', 'Gemilang', 'Harmoni', 'Ikhlas', 'Jujur']),
            'form_level' => $level,
            'academic_year' => (string) now()->year,
        ];
    }
}
