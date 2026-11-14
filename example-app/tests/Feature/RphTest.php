<?php

use App\Models\RphDocument;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\Ai\AiGenerationException;
use App\Services\Ai\LlmClient;
use App\Services\Ai\OpenAiCompatibleClient;
use App\Services\Rph\RphGenerationService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['ai.provider' => 'demo']);

    $this->schoolClass = SchoolClass::factory()->create(['name' => '1 Amanah']);
    $this->subject = Subject::factory()->create(['name' => 'Science']);
    $this->teacher = teacherTeaching($this->schoolClass, $this->subject);
});

function rphInput(array $overrides = []): array
{
    return array_merge([
        'language' => 'ms',
        'subject_id' => test()->subject->id,
        'school_class_id' => test()->schoolClass->id,
        'topic' => 'Photosynthesis',
        'lesson_date' => '2026-10-12',
        'duration_minutes' => 60,
        'student_count' => 30,
        'learning_objectives' => 'Students can explain the basic process of photosynthesis.',
        'teaching_method' => 'Inquiry-based learning',
    ], $overrides);
}

function rphDocumentPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'RPH: Photosynthesis (1 Amanah)',
        'subject_id' => test()->subject->id,
        'school_class_id' => test()->schoolClass->id,
        'topic' => 'Photosynthesis',
        'lesson_date' => '2026-10-12',
        'duration_minutes' => 60,
        'student_count' => 30,
        'content' => [
            'learning_objectives' => "Explain photosynthesis\nName the products",
            'teacher_activities' => 'Explains the process',
            'assessment' => 'Exit ticket',
            'reflection' => '',
        ],
    ], $overrides);
}

test('the RPH generator form shows the review notice', function () {
    $this->actingAs($this->teacher->user)
        ->get(route('teacher.rph.create'))
        ->assertOk()
        ->assertSee('AI-generated content should be reviewed and edited by the teacher before use.');
});

test('generating an RPH stores a draft in the session for review, not in the database', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), rphInput())
        ->assertRedirect(route('teacher.rph.draft'))
        ->assertSessionHas('success');

    expect(RphDocument::count())->toBe(0);

    $draft = session('rph_draft');
    expect($draft['content']['learning_objectives'])->toBe(['Students can explain the basic process of photosynthesis.'])
        ->and($draft['content']['teaching_activities'])->toHaveCount(3)
        ->and($draft['provider'])->toBe('demo');

    $this->get(route('teacher.rph.draft'))
        ->assertOk()
        ->assertSee('Review Your RPH Draft')
        ->assertSee('Photosynthesis')
        ->assertSee('AI-generated content should be reviewed');
});

test('RPH generation validates the teacher input', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), rphInput(['topic' => '', 'duration_minutes' => 5]))
        ->assertSessionHasErrors(['topic', 'duration_minutes']);
});

test('a teacher can generate an RPH from just the topic and the AI suggests the objectives', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), [
            'language' => 'en',
            'subject_id' => $this->subject->id,
            'school_class_id' => $this->schoolClass->id,
            'topic' => 'Photosynthesis',
            'lesson_date' => '2026-10-12',
            'duration_minutes' => 60,
        ])
        ->assertRedirect(route('teacher.rph.draft'));

    $draft = session('rph_draft');

    expect($draft['content']['learning_objectives'])->not->toBeEmpty()
        ->and($draft['content']['learning_objectives'][0])->toContain('Photosynthesis');
});

test('the RPH form keeps the extra fields in an optional section', function () {
    $this->actingAs($this->teacher->user)
        ->get(route('teacher.rph.create'))
        ->assertOk()
        ->assertSee('What will you teach?')
        ->assertSee('More options');
});

test('a teacher cannot generate an RPH for a class they do not teach', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), rphInput(['school_class_id' => SchoolClass::factory()->create()->id]))
        ->assertSessionHasErrors('school_class_id');
});

test('the OpenAI-compatible provider sends the RPH prompt and parses the JSON reply', function () {
    config(['ai.provider' => 'openai', 'ai.api_key' => 'test-key', 'ai.model' => 'test-model', 'ai.api_url' => 'https://llm.example.test/v1']);

    Http::fake([
        'llm.example.test/*' => Http::response(['choices' => [['message' => ['content' => "```json\n".json_encode([
            'learning_objectives' => ['Explain photosynthesis'],
            'teacher_activities' => ['Introduce the topic'],
            'student_activities' => ['Group discussion'],
            'assessment' => 'Quiz',
        ])."\n```"]]]]),
    ]);

    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), rphInput())
        ->assertRedirect(route('teacher.rph.draft'));

    Http::assertSent(fn (Request $request) => $request->url() === 'https://llm.example.test/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer test-key')
        && $request['model'] === 'test-model'
        && str_contains($request['messages'][0]['content'], 'Rancangan Pengajaran Harian')
        && str_contains($request['messages'][1]['content'], 'Photosynthesis'));

    expect(session('rph_draft.content.teacher_activities'))->toBe(['Introduce the topic'])
        ->and(session('rph_draft.content.closure'))->toBe('')
        ->and(session('rph_draft.provider'))->toBe('openai:test-model');
});

test('a provider failure shows a friendly error and keeps the form input', function () {
    config(['ai.provider' => 'openai', 'ai.api_key' => 'k', 'ai.model' => 'm', 'ai.api_url' => 'https://llm.example.test/v1']);
    Http::fake(['*' => Http::response('Server error', 500)]);

    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), rphInput())
        ->assertRedirect(route('teacher.rph.create'))
        ->assertSessionHas('error', 'The AI service returned an error (HTTP 500). Please try again later.')
        ->assertSessionHasInput('topic', 'Photosynthesis');
});

test('the service rejects replies that are not JSON', function () {
    $client = Mockery::mock(LlmClient::class);
    $client->shouldReceive('complete')->andReturn('Sorry, I cannot help with that.');

    (new RphGenerationService($client))->generate(['subject' => 'Science', 'class' => '1 Amanah', 'topic' => 'X', 'lesson_date' => '2026-10-12', 'duration_minutes' => 60]);
})->throws(AiGenerationException::class);

test('the provider can be swapped through configuration', function () {
    config(['ai.provider' => 'openai', 'ai.model' => 'some-model']);

    expect(app(LlmClient::class))->toBeInstanceOf(OpenAiCompatibleClient::class);
});

test('saving a reviewed draft creates an AI-generated RPH and clears the draft', function () {
    $this->actingAs($this->teacher->user)->post(route('teacher.rph.generate'), rphInput());

    $response = $this->post(route('teacher.rph.store'), rphDocumentPayload());

    $document = RphDocument::sole();
    $response->assertRedirect(route('teacher.rph.show', $document))->assertSessionHas('success', 'RPH saved successfully.');

    expect($document->teacher_id)->toBe($this->teacher->id)
        ->and($document->is_ai_generated)->toBeTrue()
        ->and($document->ai_provider)->toBe('demo')
        ->and($document->content['learning_objectives'])->toBe(['Explain photosynthesis', 'Name the products'])
        ->and($document->content['teacher_activities'])->toBe(['Explains the process'])
        ->and($document->inputs['topic'])->toBe('Photosynthesis')
        ->and(session()->has('rph_draft'))->toBeFalse();
});

test('a teacher can edit a saved RPH', function () {
    $document = RphDocument::factory()->for($this->teacher)->create(['subject_id' => $this->subject->id, 'school_class_id' => $this->schoolClass->id]);

    $this->actingAs($this->teacher->user)
        ->put(route('teacher.rph.update', $document), rphDocumentPayload(['title' => 'Edited RPH', 'content' => ['learning_objectives' => 'New objective', 'reflection' => 'Went well']]))
        ->assertRedirect(route('teacher.rph.show', $document));

    $document->refresh();

    expect($document->title)->toBe('Edited RPH')
        ->and($document->content['learning_objectives'])->toBe(['New objective'])
        ->and($document->content['reflection'])->toBe('Went well');
});

test('learning objectives are required when saving', function () {
    $document = RphDocument::factory()->for($this->teacher)->create();

    $this->actingAs($this->teacher->user)
        ->put(route('teacher.rph.update', $document), rphDocumentPayload(['content' => ['learning_objectives' => '']]))
        ->assertSessionHasErrors('content.learning_objectives');
});

test('a teacher can delete an RPH', function () {
    $document = RphDocument::factory()->for($this->teacher)->create();

    $this->actingAs($this->teacher->user)
        ->delete(route('teacher.rph.destroy', $document))
        ->assertRedirect(route('teacher.rph.index'));

    $this->assertModelMissing($document);
});

test('a teacher can view, print and download an RPH', function () {
    $document = RphDocument::factory()->for($this->teacher)->create(['topic' => 'Photosynthesis', 'title' => 'RPH Photosynthesis']);

    $this->actingAs($this->teacher->user);

    $this->get(route('teacher.rph.show', $document))->assertOk()->assertSee('RANCANGAN PENGAJARAN HARIAN');
    $this->get(route('teacher.rph.print', $document))->assertOk()->assertSee($this->teacher->user->name)->assertSee('Print / Save as PDF');
    $this->get(route('teacher.rph.download', $document))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/msword')
        ->assertDownload('rph-photosynthesis.doc');
});

test('My RPH can be searched and filtered', function () {
    RphDocument::factory()->for($this->teacher)->create(['title' => 'RPH Photosynthesis', 'topic' => 'Photosynthesis', 'subject_id' => $this->subject->id]);
    RphDocument::factory()->for($this->teacher)->create(['title' => 'RPH Fractions', 'topic' => 'Fractions']);

    $this->actingAs($this->teacher->user)
        ->get(route('teacher.rph.index', ['search' => 'Photo']))
        ->assertSee('RPH Photosynthesis')
        ->assertDontSee('RPH Fractions');

    $this->get(route('teacher.rph.index', ['subject_id' => $this->subject->id]))
        ->assertSee('RPH Photosynthesis')
        ->assertDontSee('RPH Fractions');
});

test('the demo generator writes the draft in the chosen language', function (string $language, string $expected) {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), rphInput(['language' => $language]))
        ->assertRedirect(route('teacher.rph.draft'));

    expect(session('rph_draft.content.teaching_activities.0'))->toStartWith($expected);
})->with([
    'Bahasa Melayu' => ['ms', 'Set induksi'],
    'English' => ['en', 'Set induction'],
]);

test('the RPH language must be Bahasa Melayu or English', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), rphInput(['language' => 'fr']))
        ->assertSessionHasErrors('language');
});

test('the AI prompt asks for SPI 3/1999 elements and the chosen language', function () {
    config(['ai.provider' => 'openai', 'ai.api_key' => 'k', 'ai.model' => 'm', 'ai.api_url' => 'https://llm.example.test/v1']);
    Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => '{"learning_objectives":["x"]}']]]])]);

    $this->actingAs($this->teacher->user)->post(route('teacher.rph.generate'), rphInput(['theme' => 'Sains Hayat', 'week' => 41]));

    Http::assertSent(fn (Request $request) => str_contains($request['messages'][0]['content'], 'Surat Pekeliling Ikhtisas Bil. 3/1999')
        && str_contains($request['messages'][1]['content'], '"language": "Bahasa Melayu"')
        && str_contains($request['messages'][1]['content'], '"theme": "Sains Hayat"'));
});

test('header fields from the generator carry over to the draft and saved RPH', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.rph.generate'), rphInput(['theme' => 'Sains Hayat', 'unit' => '5', 'week' => 41, 'start_time' => '08:40']));

    $this->get(route('teacher.rph.draft'))->assertSee('Sains Hayat')->assertSee('08:40');

    $this->post(route('teacher.rph.store'), rphDocumentPayload(['theme' => 'Sains Hayat', 'unit' => '5', 'week' => 41, 'start_time' => '08:40']));

    $document = RphDocument::sole();
    expect($document->theme)->toBe('Sains Hayat')
        ->and($document->week)->toBe(41)
        ->and($document->timeRange())->toBe('8:40 AM – 9:40 AM');
});

test('the printed RPH follows the KPM template layout', function () {
    $document = RphDocument::factory()->for($this->teacher)->create([
        'subject_id' => $this->subject->id,
        'school_class_id' => $this->schoolClass->id,
        'lesson_date' => '2026-10-12',
        'week' => 41,
        'student_count' => 30,
        'theme' => 'Sains Hayat',
        'content' => ['learning_objectives' => ['Memberi contoh tumbuhan.'], 'reflection' => ''],
    ]);

    $this->actingAs($this->teacher->user)
        ->get(route('teacher.rph.print', $document))
        ->assertOk()
        ->assertSeeInOrder(['MATA PELAJARAN', 'MINGGU', 'MASA', 'TARIKH / HARI', 'UNIT', 'KELAS', 'TEMA', 'BIL. MURID', 'TAJUK'])
        ->assertSee('12 Oktober 2026 / Isnin')
        ->assertSeeInOrder(['OBJEKTIF PEMBELAJARAN', 'Pada akhir pengajaran dan pembelajaran murid dapat:', 'Memberi contoh tumbuhan.'])
        ->assertSee('AKTIVITI PENGAJARAN DAN PEMBELAJARAN')
        ->assertSee('_____/30 murid dapat mencapai objektif pembelajaran yang ditetapkan.');
});
