<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Finds every piece of text that needs a translation: static text in Blade views
 * (what BladeTranslator wraps) plus literal __('...') calls in views and PHP code.
 */
class TranslationKeys
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $keys = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());

            foreach (BladeTranslator::collect($contents) as $key) {
                $keys[$key] = true;
            }

            foreach (self::literalCalls($contents) as $key) {
                $keys[$key] = true;
            }
        }

        foreach (File::allFiles(app_path()) as $file) {
            foreach (self::literalCalls(File::get($file->getPathname())) as $key) {
                $keys[$key] = true;
            }
        }

        $keys = array_keys($keys);
        sort($keys, SORT_NATURAL | SORT_FLAG_CASE);

        return $keys;
    }

    /**
     * Keys that have no translation in lang/{locale}.json yet.
     *
     * @return list<string>
     */
    public static function missing(string $locale): array
    {
        $path = lang_path($locale.'.json');
        $translated = is_file($path) ? json_decode(File::get($path), true) ?? [] : [];

        return array_values(array_filter(self::all(), fn (string $key): bool => ! array_key_exists($key, $translated)));
    }

    /**
     * @return list<string>
     */
    private static function literalCalls(string $code): array
    {
        $found = [];

        preg_match_all('/\b__\(\s*\'((?:[^\'\\\\]|\\\\.)*)\'/', $code, $single);
        preg_match_all('/\b__\(\s*"((?:[^"\\\\]|\\\\.)*)"/', $code, $double);

        foreach ($single[1] as $text) {
            $found[] = stripcslashes($text);
        }

        foreach ($double[1] as $text) {
            $found[] = stripcslashes($text);
        }

        return array_values(array_filter($found, fn (string $text): bool => preg_match('/[A-Za-z]/', $text) === 1));
    }
}
