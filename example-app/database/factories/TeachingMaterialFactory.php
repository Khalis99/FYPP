<?php

namespace Database\Factories;

use App\Enums\MaterialType;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingMaterial>
 */
class TeachingMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->slug(3).'.pdf';

        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'school_class_id' => SchoolClass::factory(),
            'title' => fake()->sentence(4),
            'topic' => fake()->words(2, true),
            'type' => fake()->randomElement(MaterialType::cases()),
            'description' => fake()->sentence(),
            'file_path' => 'materials/'.$fileName,
            'file_name' => $fileName,
            'file_size' => fake()->numberBetween(20_000, 2_000_000),
        ];
    }
}
