<?php

use App\Support\BladeTranslator;

/** Shows the generated call as the familiar __() so the expectations stay readable. */
function translated(string $template): string
{
    return str_replace('\\App\\Support\\Translate::text(', '__(', BladeTranslator::apply($template));
}

test('plain text and its surrounding whitespace are wrapped', function () {
    expect(translated("<h1>\n    Save changes\n</h1>"))
        ->toBe("<h1>\n    {{ __('Save changes') }}\n</h1>");
});

test('wrapped text keeps its position next to Blade expressions', function () {
    expect(translated('<p>Page {{ $page }} of {{ $total }}</p>'))
        ->toBe("<p>{{ __('Page') }} {{ \$page }} {{ __('of') }} {{ \$total }}</p>");
});

test('multi-line text becomes one key and quotes are escaped', function () {
    expect(BladeTranslator::collect("<p>\n  You don't have\n  any items.\n</p>"))
        ->toBe(["You don't have any items."]);

    expect(translated("<p>Today's plan</p>"))
        ->toBe("<p>{{ __('Today\\'s plan') }}</p>");
});

test('html entities are decoded for the key', function () {
    expect(BladeTranslator::collect('<p>Tom &amp; Jerry</p>'))->toBe(['Tom & Jerry']);
    expect(BladeTranslator::collect('<span>&times;</span>'))->toBe([]);
});

test('static attributes are translated on html tags and components', function () {
    expect(translated('<input placeholder="e.g. Algebra" class="form-input">'))
        ->toBe('<input placeholder="{{ __(\'e.g. Algebra\') }}" class="form-input">');

    expect(translated('<x-card title="Recent Announcements" :padding="false"></x-card>'))
        ->toBe('<x-card :title="__(\'Recent Announcements\')" :padding="false"></x-card>');

    expect(BladeTranslator::collect('<a href="/x" title="Open it" class="Big">Go</a>'))->toBe(['Open it', 'Go']);
});

test('bound attributes and attributes with expressions are left alone', function () {
    $template = '<x-card :title="$title" label="{{ $x }}" class="Some Class" name="Not text"></x-card>';

    expect(translated($template))->toBe($template);
});

test('php, directives, comments, scripts and styles are never touched', function () {
    $template = <<<'BLADE'
{{-- A comment about things --}}
@php
    $a = 1 < 2 ? 'Yes' : 'No';
@endphp
@if ($count > 0 && $name === 'Anna')
    <script>const message = 'Hello world';</script>
    <style>.a > .b { color: red; }</style>
    <x-sidebar-link :href="route('home')" :active="request()->routeIs('home')">{{ $label }}</x-sidebar-link>
@endif
BLADE;

    expect(BladeTranslator::collect($template))->toBe([]);
    expect(translated($template))->toBe($template);
});

test('text inside a directive branch is translated', function () {
    expect(translated('@if ($ok)<b>Yes</b>@else<b>No</b>@endif'))
        ->toBe("@if (\$ok)<b>{{ __('Yes') }}</b>@else<b>{{ __('No') }}</b>@endif");
});

test('email-like text is not mistaken for a directive', function () {
    expect(BladeTranslator::collect('<p>Write to teacher@school.edu.my today</p>'))
        ->toBe(['Write to teacher@school.edu.my today']);
});

test('text without letters is skipped', function () {
    expect(BladeTranslator::collect('<td>12 / 30 · 40%</td>'))->toBe([]);
});

test('templates marked i18n:off are returned unchanged', function () {
    $template = "{{-- i18n:off --}}\n<p>Official form text</p>";

    expect(translated($template))->toBe($template);
});

test('applying it twice does not translate the translation calls again', function () {
    $once = translated('<p>Hello there</p>');

    expect(translated($once))->toBe($once);
});
