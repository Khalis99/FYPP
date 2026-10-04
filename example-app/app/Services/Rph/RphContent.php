<?php

namespace App\Services\Rph;

/**
 * Describes the editable sections of an RPH and converts between the stored
 * array format and the textarea-based form (one list item per line).
 */
class RphContent
{
    /**
     * Section key => [label, is_list].
     *
     * @var array<string, array{0: string, 1: bool}>
     */
    public const SECTIONS = [
        'learning_objectives' => ['Learning Objectives', true],
        'learning_standards' => ['Learning Standards', true],
        'previous_knowledge' => ['Previous Knowledge', false],
        'teaching_aids' => ['Teaching Aids', true],
        'teaching_activities' => ['Teaching Activities', true],
        'teacher_activities' => ['Teacher Activities', true],
        'student_activities' => ['Student Activities', true],
        'assessment' => ['Assessment', false],
        'closure' => ['Closure', false],
        'reflection' => ['Reflection', false],
    ];

    /**
     * Make any array (AI output or form input) conform to the section structure.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string|list<string>>
     */
    public static function normalize(array $data): array
    {
        $content = [];

        foreach (self::SECTIONS as $key => [, $isList]) {
            $value = $data[$key] ?? ($isList ? [] : '');

            $content[$key] = $isList ? self::toList($value) : self::toText($value);
        }

        return $content;
    }

    /**
     * Convert stored content into strings suitable for textareas.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, string>
     */
    public static function toForm(array $content): array
    {
        $form = [];

        foreach (self::normalize($content) as $key => $value) {
            $form[$key] = is_array($value) ? implode("\n", $value) : $value;
        }

        return $form;
    }

    /**
     * @return list<string>
     */
    private static function toList(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/\R/', $value) ?: [];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (mixed $item): string => trim(is_scalar($item) ? (string) $item : json_encode($item)), $value),
            fn (string $item): bool => $item !== '',
        ));
    }

    private static function toText(mixed $value): string
    {
        if (is_array($value)) {
            return implode("\n", self::toList($value));
        }

        return trim((string) $value);
    }
}
