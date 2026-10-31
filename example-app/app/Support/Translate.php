<?php

namespace App\Support;

/**
 * Translation call emitted by BladeTranslator.
 *
 * __() returns a whole language file (an array) when the text happens to match a file name such as
 * "Pagination" or "Validation", which would break the page. This always returns text.
 */
final class Translate
{
    /**
     * @param  array<string, string|int>  $replace
     */
    public static function text(string $key, array $replace = []): string
    {
        $line = __($key, $replace);

        return is_string($line) ? $line : $key;
    }
}
