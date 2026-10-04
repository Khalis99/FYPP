<?php

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeachingMaterial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    $this->schoolClass = SchoolClass::factory()->create();
    $this->subject = Subject::factory()->create();
    $this->teacher = teacherTeaching($this->schoolClass, $this->subject);
});

function materialPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Chapter 3 Algebra Notes',
        'subject_id' => test()->subject->id,
        'school_class_id' => test()->schoolClass->id,
        'topic' => 'Algebra',
        'type' => 'notes',
        'description' => 'Notes for chapter 3',
        'file' => UploadedFile::fake()->create('algebra.pdf', 200, 'application/pdf'),
    ], $overrides);
}

test('a teacher can upload a teaching material', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.materials.store'), materialPayload())
        ->assertRedirect()
        ->assertSessionHas('success', 'Teaching material uploaded successfully.');

    $material = TeachingMaterial::sole();

    expect($material->teacher_id)->toBe($this->teacher->id)
        ->and($material->file_name)->toBe('algebra.pdf');

    Storage::disk('local')->assertExists($material->file_path);
});

test('uploads reject files with a type that is not allowed', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.materials.store'), materialPayload(['file' => UploadedFile::fake()->create('virus.exe', 10)]))
        ->assertSessionHasErrors('file');

    expect(TeachingMaterial::count())->toBe(0);
});

test('uploads reject files that are too large', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.materials.store'), materialPayload(['file' => UploadedFile::fake()->create('big.pdf', 20_000, 'application/pdf')]))
        ->assertSessionHasErrors('file');
});

test('a file and required fields are needed when uploading', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.materials.store'), ['title' => '', 'type' => 'invalid'])
        ->assertSessionHasErrors(['title', 'subject_id', 'type', 'file']);
});

test('a teacher cannot upload for a class they do not teach', function () {
    $otherClass = SchoolClass::factory()->create();

    $this->actingAs($this->teacher->user)
        ->post(route('teacher.materials.store'), materialPayload(['school_class_id' => $otherClass->id]))
        ->assertSessionHasErrors('school_class_id');
});

test('a teacher can download their material', function () {
    Storage::disk('local')->put('materials/notes.pdf', 'pdf-content');
    $material = TeachingMaterial::factory()->for($this->teacher)->create(['file_path' => 'materials/notes.pdf', 'file_name' => 'My Notes.pdf']);

    $this->actingAs($this->teacher->user)
        ->get(route('teacher.materials.download', $material))
        ->assertOk()
        ->assertDownload('My Notes.pdf');
});

test('a teacher can edit material details and replace the file', function () {
    Storage::disk('local')->put('materials/old.pdf', 'old');
    $material = TeachingMaterial::factory()->for($this->teacher)->create([
        'subject_id' => $this->subject->id,
        'school_class_id' => $this->schoolClass->id,
        'file_path' => 'materials/old.pdf',
    ]);

    $this->actingAs($this->teacher->user)
        ->put(route('teacher.materials.update', $material), materialPayload([
            'title' => 'Updated Title',
            'file' => UploadedFile::fake()->create('new.pdf', 50, 'application/pdf'),
        ]))
        ->assertRedirect(route('teacher.materials.show', $material));

    $material->refresh();

    expect($material->title)->toBe('Updated Title')->and($material->file_name)->toBe('new.pdf');
    Storage::disk('local')->assertMissing('materials/old.pdf');
    Storage::disk('local')->assertExists($material->file_path);
});

test('a teacher can delete a material and its file', function () {
    Storage::disk('local')->put('materials/delete-me.pdf', 'x');
    $material = TeachingMaterial::factory()->for($this->teacher)->create(['file_path' => 'materials/delete-me.pdf']);

    $this->actingAs($this->teacher->user)
        ->delete(route('teacher.materials.destroy', $material))
        ->assertRedirect(route('teacher.materials.index'));

    $this->assertModelMissing($material);
    Storage::disk('local')->assertMissing('materials/delete-me.pdf');
});

test('materials can be searched and filtered', function () {
    TeachingMaterial::factory()->for($this->teacher)->create(['title' => 'Algebra Notes', 'type' => 'notes']);
    TeachingMaterial::factory()->for($this->teacher)->create(['title' => 'Geometry Slides', 'topic' => 'Shapes', 'type' => 'slides']);

    $this->actingAs($this->teacher->user)
        ->get(route('teacher.materials.index', ['search' => 'Algebra']))
        ->assertSee('Algebra Notes')
        ->assertDontSee('Geometry Slides');

    $this->actingAs($this->teacher->user)
        ->get(route('teacher.materials.index', ['type' => 'slides']))
        ->assertSee('Geometry Slides')
        ->assertDontSee('Algebra Notes');
});
