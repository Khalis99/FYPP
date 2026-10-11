<?php

namespace App\Services\Ai;

/**
 * Offline stand-in for a real model, used when AI_PROVIDER=demo.
 *
 * It reads the teacher's input (sent as JSON at the end of the user message)
 * and fills a fixed RPH template in Bahasa Melayu or English, so the full
 * generate → edit → save flow can be demonstrated without an API key.
 */
class DemoLlmClient implements LlmClient
{
    public function complete(string $systemPrompt, string $userMessage): string
    {
        $input = json_decode(substr($userMessage, (int) strpos($userMessage, '{')), true) ?: [];

        $minutes = (int) ($input['duration_minutes'] ?? 60);
        $timing = [
            'induction' => $induction = max(5, (int) round($minutes * 0.1)),
            'closure' => $closure = max(5, (int) round($minutes * 0.15)),
            'development' => max($minutes - $induction - $closure, 10),
        ];

        $content = ($input['language'] ?? '') === 'English'
            ? $this->english($input['topic'] ?? 'the topic', $timing)
            : $this->malay($input['topic'] ?? 'topik ini', $timing);

        return json_encode([
            'subject' => $input['subject'] ?? '',
            'class' => $input['class'] ?? '',
            'topic' => $input['topic'] ?? '',
            'duration' => "{$minutes} minutes",
            'learning_objectives' => $this->lines($input['learning_objectives'] ?? '') ?: $content['learning_objectives'],
            'learning_standards' => $this->lines($input['learning_standards'] ?? ''),
            'previous_knowledge' => ($input['previous_knowledge'] ?? '') ?: $content['previous_knowledge'],
            'teaching_aids' => $this->lines($input['teaching_aids'] ?? '') ?: $content['teaching_aids'],
            'teaching_activities' => $content['teaching_activities'],
            'teacher_activities' => $content['teacher_activities'],
            'student_activities' => $content['student_activities'],
            'assessment' => $content['assessment'],
            'closure' => $content['closure'],
            'reflection' => '',
        ]);
    }

    public function name(): string
    {
        return 'demo';
    }

    /**
     * @param  array{induction: int, development: int, closure: int}  $t
     * @return array<string, string|list<string>>
     */
    private function malay(string $topic, array $t): array
    {
        return [
            'learning_objectives' => ["Menerangkan idea utama {$topic}."],
            'previous_knowledge' => "Murid pernah melihat contoh berkaitan {$topic} dalam kehidupan seharian.",
            'teaching_aids' => ['Buku teks', 'Papan putih dan pen marker', 'Lembaran kerja'],
            'teaching_activities' => [
                "Set induksi ({$t['induction']} minit): Guru menunjukkan contoh sebenar berkaitan {$topic} dan bertanya apa yang murid perhatikan.",
                "Perkembangan ({$t['development']} minit): Guru menerangkan konsep utama {$topic} langkah demi langkah. Murid dibahagikan kepada kumpulan kecil untuk melaksanakan tugasan berpandu.",
                "Penutup ({$t['closure']} minit): Setiap kumpulan membentangkan jawapan dan guru merumuskan isi penting pelajaran.",
            ],
            'teacher_activities' => [
                "Mengemukakan soalan pembuka untuk mengaitkan {$topic} dengan pengetahuan sedia ada murid.",
                'Menerangkan konsep utama menggunakan bahan bantu mengajar.',
                'Membimbing kumpulan dan menyemak kefahaman murid semasa aktiviti.',
                'Merumuskan pelajaran dan memberi maklum balas.',
            ],
            'student_activities' => [
                'Menjawab soalan pembuka dan berkongsi idea.',
                'Mencatat isi penting.',
                'Melaksanakan tugasan kumpulan dan membentangkan jawapan.',
                'Bersoal jawab dengan guru tentang perkara yang kurang jelas.',
            ],
            'assessment' => "Soal jawab lisan semasa PdP dan satu soalan tiket keluar berkaitan {$topic}. Pencapaian objektif dinilai berdasarkan jawapan murid.",
            'closure' => "Membuat rumusan isi penting {$topic} dan mengaitkannya dengan pelajaran seterusnya.",
        ];
    }

    /**
     * @param  array{induction: int, development: int, closure: int}  $t
     * @return array<string, string|list<string>>
     */
    private function english(string $topic, array $t): array
    {
        return [
            'learning_objectives' => ["Explain the key ideas of {$topic}."],
            'previous_knowledge' => "Students have encountered everyday examples related to {$topic}.",
            'teaching_aids' => ['Textbook', 'Whiteboard and markers', 'Worksheet'],
            'teaching_activities' => [
                "Set induction ({$t['induction']} min): Show a short real-life example related to {$topic} and ask students what they notice.",
                "Development ({$t['development']} min): Explain the main concepts of {$topic} step by step, then students work in small groups on a guided task.",
                "Closure ({$t['closure']} min): Groups share answers and the teacher summarises the key points.",
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
        ];
    }

    /**
     * @return list<string>
     */
    private function lines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $text) ?: [])));
    }
}
