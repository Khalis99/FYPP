<?php

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Timetable;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->schoolClass = SchoolClass::factory()->create();
    $this->subject = Subject::factory()->create();
    $this->teacher = teacherTeaching($this->schoolClass, $this->subject);
});

function timetablePayload(array $overrides = []): array
{
    return array_merge([
        'day' => 1,
        'start_time' => '08:00',
        'end_time' => '09:00',
        'teacher_id' => test()->teacher->id,
        'subject_id' => test()->subject->id,
        'school_class_id' => test()->schoolClass->id,
        'room' => 'Room 201',
    ], $overrides);
}

test('an admin can create, update and delete timetable entries', function () {
    $this->actingAs(adminUser())->post(route('admin.timetable.store'), timetablePayload())->assertRedirect();

    $entry = Timetable::sole();
    expect($entry->room)->toBe('Room 201');

    $this->put(route('admin.timetable.update', $entry), timetablePayload(['start_time' => '10:00', 'end_time' => '11:00']))
        ->assertRedirect(route('admin.timetable.index'));
    expect($entry->fresh()->start_time)->toStartWith('10:00');

    $this->delete(route('admin.timetable.destroy', $entry))->assertSessionHas('success');
    $this->assertModelMissing($entry);
});

test('the end time must be after the start time', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.timetable.store'), timetablePayload(['start_time' => '10:00', 'end_time' => '09:00']))
        ->assertSessionHasErrors('end_time');
});

test('a teacher cannot be double-booked', function () {
    $otherClass = SchoolClass::factory()->create();
    $this->teacher->teachingLoads()->create(['school_class_id' => $otherClass->id, 'subject_id' => $this->subject->id]);
    Timetable::factory()->create(timetablePayload());

    $this->actingAs(adminUser())
        ->post(route('admin.timetable.store'), timetablePayload(['school_class_id' => $otherClass->id, 'start_time' => '08:30', 'end_time' => '09:30', 'room' => 'Room 999']))
        ->assertSessionHasErrors(['start_time' => 'This teacher already has a class at this time.']);
});

test('a class and a room cannot be double-booked', function () {
    $otherSubject = Subject::factory()->create();
    $otherTeacher = teacherTeaching($this->schoolClass, $otherSubject);
    Timetable::factory()->create(timetablePayload());

    $this->actingAs(adminUser())
        ->post(route('admin.timetable.store'), timetablePayload(['teacher_id' => $otherTeacher->id, 'subject_id' => $otherSubject->id]))
        ->assertSessionHasErrors(['start_time', 'room']);
});

test('back-to-back lessons are allowed', function () {
    Timetable::factory()->create(timetablePayload());

    $this->actingAs(adminUser())
        ->post(route('admin.timetable.store'), timetablePayload(['start_time' => '09:00', 'end_time' => '10:00']))
        ->assertSessionDoesntHaveErrors();

    expect(Timetable::count())->toBe(2);
});

test('the teacher must be assigned to the subject in that class', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.timetable.store'), timetablePayload(['school_class_id' => SchoolClass::factory()->create()->id]))
        ->assertSessionHasErrors('teacher_id');
});

test('updating an entry does not conflict with itself', function () {
    $entry = Timetable::factory()->create(timetablePayload());

    $this->actingAs(adminUser())
        ->put(route('admin.timetable.update', $entry), timetablePayload(['room' => 'Room 305']))
        ->assertSessionDoesntHaveErrors();
});

test('a teacher sees their own weekly timetable', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-05 07:00')); // Monday
    Timetable::factory()->create(timetablePayload(['room' => 'Science Lab 1']));
    Timetable::factory()->create(['room' => 'Someone Else Room']);

    $this->actingAs($this->teacher->user)
        ->get(route('teacher.timetable.index'))
        ->assertOk()
        ->assertSee('8:00 AM – 9:00 AM')
        ->assertSee('Science Lab 1')
        ->assertDontSee('Someone Else Room')
        ->assertSee('Today');
});
