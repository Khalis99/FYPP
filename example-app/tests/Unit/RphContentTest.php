<?php

use App\Services\Rph\RphContent;

test('normalize fills every section and converts text to lists where needed', function () {
    $content = RphContent::normalize([
        'learning_objectives' => "First objective\n\n  Second objective  ",
        'assessment' => ['Quiz', 'Oral questions'],
        'unexpected_key' => 'ignored',
    ]);

    expect(array_keys($content))->toBe(array_keys(RphContent::SECTIONS))
        ->and($content['learning_objectives'])->toBe(['First objective', 'Second objective'])
        ->and($content['assessment'])->toBe("Quiz\nOral questions")
        ->and($content['teaching_aids'])->toBe([])
        ->and($content['closure'])->toBe('');
});

test('toForm turns lists into one item per line', function () {
    $form = RphContent::toForm(['teaching_aids' => ['Textbook', 'Video']]);

    expect($form['teaching_aids'])->toBe("Textbook\nVideo")
        ->and($form['reflection'])->toBe('');
});
