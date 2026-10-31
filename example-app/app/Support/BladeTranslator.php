<?php

namespace App\Support;

/**
 * Makes the plain English text in Blade views translatable without editing every template.
 *
 * It runs while a view is being compiled (see AppServiceProvider) and wraps static text and a few
 * static attributes in a translation call, so "Save changes" becomes {{ Translate::text('Save changes') }}. The English text in
 * the template is the translation key; lang/ms.json holds the Bahasa Melayu version. Text with no
 * translation simply stays English, and PHP code, Blade tags, scripts and styles are never touched.
 *
 * A template can opt out entirely by containing {{-- i18n:off --}} (used for the official RPH form).
 */
class BladeTranslator
{
    private const OFF_MARKER = '{{-- i18n:off --}}';

    /** Static HTML attributes whose text is translated. */
    private const HTML_ATTRIBUTES = ['placeholder', 'title', 'aria-label', 'alt', 'data-loading-text', 'data-confirm', 'data-confirm-button'];

    /** Static attributes of <x-...> components whose text is translated (they become :attr="Translate::text('...')"). */
    private const COMPONENT_ATTRIBUTES = ['title', 'label', 'description', 'message', 'hint', 'placeholder'];

    /** Matches a whole HTML or component tag, treating quoted attribute values as opaque. */
    private const TAG_PATTERN = '/(<\/?[a-zA-Z][^\s>\/]*(?:"[^"]*"|\'[^\']*\'|[^>"\'])*>)/s';

    /** @var list<string> Chunks hidden from the translator while the template is processed. */
    private array $masked = [];

    /** @var (callable(string): void)|null */
    private $collector = null;

    public static function apply(string $template): string
    {
        return (new self)->transform($template);
    }

    /**
     * The translation keys a template contains (used by the translations:missing command and tests).
     *
     * @return list<string>
     */
    public static function collect(string $template): array
    {
        $keys = [];

        (new self)->transform($template, function (string $key) use (&$keys): void {
            $keys[$key] = true;
        });

        return array_keys($keys);
    }

    /**
     * @param  (callable(string): void)|null  $collector
     */
    private function transform(string $template, ?callable $collector = null): string
    {
        if (str_contains($template, self::OFF_MARKER)) {
            return $template;
        }

        $this->masked = [];
        $this->collector = $collector;

        $source = $template;
        $source = $this->maskPattern('/\{\{--.*?--\}\}/s', $source);
        $source = $this->maskPattern('/@verbatim.*?@endverbatim/s', $source);
        $source = $this->maskPattern('/<\?php.*?\?>/s', $source);
        $source = $this->maskPattern('/@php\b(?!\s*\().*?@endphp/s', $source);
        $source = $this->maskPattern('/<(script|style)\b.*?<\/\1>/is', $source);
        $source = $this->maskPattern('/<!--.*?-->|<![a-zA-Z][^>]*>/s', $source);
        $source = $this->maskPattern('/\{!!.*?!!\}|\{\{.*?\}\}/s', $source);
        $source = $this->maskDirectives($source);

        $parts = preg_split(self::TAG_PATTERN, $source, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';

        foreach ($parts as $index => $part) {
            $result .= $index % 2 === 1 ? $this->transformTag($part) : $this->transformText($part);
        }

        // Later chunks may contain the tokens of earlier ones, so restore in reverse order.
        foreach (array_reverse(array_keys($this->masked)) as $id) {
            $result = str_replace($this->token($id), $this->masked[$id], $result);
        }

        return $result;
    }

    private function token(int $id): string
    {
        return "\x01{$id}\x02";
    }

    private function mask(string $chunk): string
    {
        $this->masked[] = $chunk;

        return $this->token(array_key_last($this->masked));
    }

    private function maskPattern(string $pattern, string $source): string
    {
        return preg_replace_callback($pattern, fn (array $m): string => $this->mask($m[0]), $source);
    }

    /** Hides @directives together with their (balanced) arguments. */
    private function maskDirectives(string $source): string
    {
        $output = '';
        $offset = 0;

        while (preg_match('/(?<![\w@])@([a-zA-Z_]\w*)/', $source, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $start = $match[0][1];
            $end = $start + strlen($match[0][0]);

            if (preg_match('/\G[ \t]*\(/', $source, $paren, 0, $end)) {
                $close = $this->matchingParenthesis($source, $end + strlen($paren[0]) - 1);

                if ($close !== null) {
                    $end = $close + 1;
                }
            }

            $output .= substr($source, $offset, $start - $offset).$this->mask(substr($source, $start, $end - $start));
            $offset = $end;
        }

        return $output.substr($source, $offset);
    }

    private function matchingParenthesis(string $source, int $open): ?int
    {
        $depth = 0;
        $quote = null;

        for ($i = $open, $length = strlen($source); $i < $length; $i++) {
            $char = $source[$i];

            if ($quote !== null) {
                if ($char === '\\') {
                    $i++;
                } elseif ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($char === '"' || $char === "'") {
                $quote = $char;
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')' && --$depth === 0) {
                return $i;
            }
        }

        return null;
    }

    private function transformTag(string $tag): string
    {
        $isComponent = preg_match('/^<x[-:]/', $tag) === 1;
        $names = $isComponent ? self::COMPONENT_ATTRIBUTES : self::HTML_ATTRIBUTES;

        return preg_replace_callback('/(\s)([\w:.@-]+)=("[^"]*"|\'[^\']*\')/', function (array $m) use ($names, $isComponent): string {
            if (! in_array($m[2], $names, true)) {
                return $m[0];
            }

            $value = substr($m[3], 1, -1);

            if (str_contains($value, "\x01") || ! $this->hasLetters($value)) {
                return $m[0];
            }

            $key = $this->normalise($value);

            if ($isComponent && str_contains($key, '"')) {
                return $m[0];
            }

            $this->collector && ($this->collector)($key);
            $call = $this->call($key);

            return $isComponent
                ? $m[1].':'.$m[2].'="'.$call.'"'
                : $m[1].$m[2].'="{{ '.$call.' }}"';
        }, $tag);
    }

    private function transformText(string $text): string
    {
        $pieces = preg_split('/(\x01\d+\x02)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($pieces as $index => $piece) {
            // Attribute-like or tag-like leftovers (for example from a dynamic <{{ $tag }} ...>) are markup, not text.
            if ($index % 2 === 1 || ! $this->hasLetters($piece) || preg_match('/[<>]|\w="/', $piece)) {
                continue;
            }

            preg_match('/^(\s*)(.*?)(\s*)$/s', $piece, $parts);

            $key = $this->normalise($parts[2]);

            $this->collector && ($this->collector)($key);
            $pieces[$index] = $parts[1].'{{ '.$this->call($key).' }}'.$parts[3];
        }

        return implode('', $pieces);
    }

    private function call(string $key): string
    {
        return "\App\Support\Translate::text('".addcslashes($key, "'\\")."')";
    }

    private function normalise(string $text): string
    {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $decoded));
    }

    private function hasLetters(string $text): bool
    {
        return preg_match('/[A-Za-z]/', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')) === 1;
    }
}
