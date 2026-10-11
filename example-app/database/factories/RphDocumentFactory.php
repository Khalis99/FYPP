<?php

namespace Database\Factories;

use App\Models\RphDocument;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RphDocument>
 */
class RphDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $topic = fake()->words(2, true);

        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'school_class_id' => SchoolClass::factory(),
            'title' => 'RPH: '.ucfirst($topic),
            'topic' => ucfirst($topic),
            'theme' => 'Sains Hayat',
            'unit' => '5',
            'lesson_date' => now()->addDays(fake()->numberBetween(0, 10))->toDateString(),
            'week' => fake()->numberBetween(1, 40),
            'start_time' => '08:00',
            'duration_minutes' => 60,
            'student_count' => 30,
            'content' => [
                'learning_objectives' => ['Students can explain '.$topic.'.'],
                'learning_standards' => ['1.1.1'],
                'previous_knowledge' => 'Students have basic knowledge of the topic.',
                'teaching_aids' => ['Textbook', 'Whiteboard'],
                'teaching_activities' => ['Set induction (5 min)', 'Main activity (45 min)', 'Closure (10 min)'],
                'teacher_activities' => ['Introduces the topic.'],
                'student_activities' => ['Answer questions in groups.'],
                'assessment' => 'Oral questioning.',
                'closure' => 'Summarise the lesson.',
                'reflection' => '',
            ],
            'is_ai_generated' => false,
        ];
    }
}
