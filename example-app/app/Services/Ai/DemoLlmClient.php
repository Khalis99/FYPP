<?php

namespace App\Services\Ai;

/**
 * Offline stand-in for a real model, used when AI_PROVIDER=demo.
 *
 * It reads the teacher's input (sent as JSON at the end of the user message)
 * and fills a fixed RPH template, so the full generate → edit → save flow
 * can be demonstrated without an API key or internet connection.
 */
class DemoLlmClient implements LlmClient
{
    public function complete(string $systemPrompt, string $userMessage): string
    {
        $input = json_decode(substr($userMessage, (int) strpos($userMessage, '{')), true) ?: [];

        $topic = $input['topic'] ?? 'the topic';
        $minutes = (int) ($input['duration_minutes'] ?? 60);
        $induction = max(5, (int) round($minutes * 0.1));
        $closure = max(5, (int) round($minutes * 0.15));
        $development = max($minutes - $induction - $closure, 10);
        $objectives = $this->lines($input['learning_objectives'] ?? '') ?: ["Students can explain the key ideas of {$topic}."];

        return json_encode([
            'subject' => $input['subject'] ?? '',
            'class' => $input['class'] ?? '',
            'topic' => $topic,
            'duration' => "{$minutes} minutes",
            'learning_objectives' => $objectives,
            'learning_standards' => $this->lines($input['learning_standards'] ?? ''),
            'previous_knowledge' => ($input['previous_knowledge'] ?? '') ?: "Students have encountered everyday examples related to {$topic}.",
            'teaching_aids' => $this->lines($input['teaching_aids'] ?? '') ?: ['Textbook', 'Whiteboard and markers', 'Worksheet'],
            'teaching_activities' => [
                "Set induction ({$induction} min): Show a short real-life example related to {$topic} and ask students what they notice.",
                "Development ({$development} min): Explain the main concepts of {$topic} step by step, then students work in small groups on a guided task.",
                "Closure ({$closure} min): Groups share answers and the teacher summarises the key points.",
            ],
            'teacher_activities' => [
                "Asks opening questions to connect {$topic} with students' prior knowledge.",
                'Explains the key concepts using the teaching aids.',
                'Guides groups and checks understanding while they work.',
                'Summarises the lesson and gives feedback.',
            ],
            'student_activities' => [
                'Answer opening questions and share their ideas.',
                'Take notes on the key concepts.',
                'Complete the group task and present their answers.',
                'Ask questions about anything unclear.',
            ],
            'assessment' => "Oral questioning during the lesson and a short exit question on {$topic}. Achievement of the objectives is judged from students' answers.",
            'closure' => "Recap the main points of {$topic} and link them to the next lesson.",
            'reflection' => '',
        ]);
    }

    public function name(): string
    {
        return 'demo';
    }

    /**
     * @return list<string>
     */
    private function lines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $text) ?: [])));
    }
}
