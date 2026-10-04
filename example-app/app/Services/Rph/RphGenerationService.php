<?php

namespace App\Services\Rph;

use App\Services\Ai\AiGenerationException;
use App\Services\Ai\LlmClient;

/**
 * Turns a teacher's lesson details into a structured RPH draft using an LLM.
 *
 * Flow: controller → this service → LlmClient (provider) → JSON → normalised content.
 * The result is always a draft that the teacher reviews before saving.
 */
class RphGenerationService
{
    public const SYSTEM_PROMPT = <<<'PROMPT'
        You are an educational assistant helping a teacher prepare a Rancangan Pengajaran Harian (RPH). Generate a structured draft based only on the information provided by the teacher. Do not invent school-specific policies. Make the activities practical for the stated class level and duration. Ensure learning objectives, activities and assessment are aligned. The teacher must review and edit the generated RPH before using it.

        Reply with a single JSON object only, with no text before or after it, using exactly these keys:
        {
          "subject": "",
          "class": "",
          "topic": "",
          "duration": "",
          "learning_objectives": [],
          "learning_standards": [],
          "previous_knowledge": "",
          "teaching_aids": [],
          "teaching_activities": [],
          "teacher_activities": [],
          "student_activities": [],
          "assessment": "",
          "closure": "",
          "reflection": ""
        }

        "teaching_activities" lists the lesson stages in order (for example set induction, development, closure), each with its duration in minutes so the total matches the lesson duration. Leave "reflection" as an empty string; the teacher writes it after the lesson. Write in the same language the teacher used.
        PROMPT;

    public function __construct(private LlmClient $client) {}

    /**
     * @param  array{subject: string, class: string, topic: string, lesson_date: string, duration_minutes: int, student_count?: int|null, learning_objectives?: string|null, learning_standards?: string|null, teaching_aids?: string|null, previous_knowledge?: string|null, teaching_method?: string|null, additional_instructions?: string|null}  $input
     * @return array<string, string|list<string>>
     *
     * @throws AiGenerationException
     */
    public function generate(array $input): array
    {
        $reply = $this->client->complete(self::SYSTEM_PROMPT, $this->buildUserMessage($input));

        return RphContent::normalize($this->decodeJson($reply));
    }

    public function providerName(): string
    {
        return $this->client->name();
    }

    /**
     * The teacher's details are sent as JSON so the model sees clearly labelled fields.
     *
     * @param  array<string, mixed>  $input
     */
    private function buildUserMessage(array $input): string
    {
        $details = array_filter($input, fn (mixed $value): bool => filled($value));

        return "Prepare an RPH draft from the teacher's lesson details below.\n\n"
            .json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Models sometimes wrap JSON in ``` fences or add a sentence around it,
     * so extract the outermost {...} block before decoding.
     *
     * @return array<string, mixed>
     */
    private function decodeJson(string $reply): array
    {
        $start = strpos($reply, '{');
        $end = strrpos($reply, '}');

        if ($start === false || $end === false || $end < $start) {
            throw new AiGenerationException('The AI response was not in the expected format. Please try generating again.');
        }

        $decoded = json_decode(substr($reply, $start, $end - $start + 1), true);

        if (! is_array($decoded)) {
            throw new AiGenerationException('The AI response could not be read. Please try generating again.');
        }

        return $decoded;
    }
}
