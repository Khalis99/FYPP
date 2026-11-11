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
        $academicYear = (string) now()->year;

        // Class names are unique per academic year, so skip names a test has already created by hand.
        $taken = SchoolClass::where('academic_year', $academicYear)->pluck('name')->all();

        for ($attempt = 0; $attempt < 50; $attempt++) {
            $level = fake()->numberBetween(1, 5);
            $name = $level.' '.fake()->randomElement(['Amanah', 'Bestari', 'Cekal', 'Dedikasi', 'Elit', 'Fikrah', 'Gemilang', 'Harmoni', 'Ikhlas', 'Jujur']);

            if (! in_array($name, $taken, true)) {
                break;
            }
        }

        return [
            'name' => $name,
            'form_level' => $level,
            'academic_year' => $academicYear,
        ];
    }
}
