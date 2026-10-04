<?php

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Notifications\AssignmentDeadlineApproaching;
use App\Notifications\SubmissionReceived;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    $this->schoolClass = SchoolClass::factory()->create();
    $this->subject = Subject::factory()->create();
    $this->teacher = teacherTeaching($this->schoolClass, $this->subject);
});

test('a teacher can create an assignment with an attachment', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.assignments.store'), [
            'title' => 'Mathematics Exercise 3',
            'subject_id' => $this->subject->id,
            'school_class_id' => $this->schoolClass->id,
            'description' => 'Algebra practice',
            'instructions' => 'Complete page 45',
            'due_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'attachment' => UploadedFile::fake()->create('exercise.docx', 100),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $assignment = Assignment::sole();

    expect($assignment->title)->toBe('Mathematics Exercise 3')
        ->and($assignment->teacher_id)->toBe($this->teacher->id)
        ->and($assignment->attachment_name)->toBe('exercise.docx');
    Storage::disk('local')->assertExists($assignment->attachment_path);
});

test('a new assignment must be due in the future', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.assignments.store'), [
            'title' => 'Late',
            'subject_id' => $this->subject->id,
            'school_class_id' => $this->schoolClass->id,
            'due_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ])
        ->assertSessionHasErrors('due_at');
});

test('a teacher cannot create an assignment for a class they do not teach', function () {
    $this->actingAs($this->teacher->user)
        ->post(route('teacher.assignments.store'), [
            'title' => 'Sneaky',
            'subject_id' => $this->subject->id,
            'school_class_id' => SchoolClass::factory()->create()->id,
            'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])
        ->assertSessionHasErrors('school_class_id');
});

test('a teacher can update and delete an assignment', function () {
    $assignment = Assignment::factory()->for($this->teacher)->create(['subject_id' => $this->subject->id, 'school_class_id' => $this->schoolClass->id]);

    $this->actingAs($this->teacher->user)
        ->put(route('teacher.assignments.update', $assignment), [
            'title' => 'Renamed',
            'subject_id' => $this->subject->id,
            'school_class_id' => $this->schoolClass->id,
            'due_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
        ])
        ->assertRedirect(route('teacher.assignments.show', $assignment));

    expect($assignment->fresh()->title)->toBe('Renamed');

    $this->delete(route('teacher.assignments.destroy', $assignment))->assertRedirect(route('teacher.assignments.index'));
    $this->assertModelMissing($assignment);
});

test('the assignment page shows submitted, late and missing students with the submission rate', function () {
    $assignment = Assignment::factory()->for($this->teacher)->create([
        'subject_id' => $this->subject->id,
        'school_class_id' => $this->schoolClass->id,
        'due_at' => now()->subDay(),
    ]);
    [$onTime, $late, $missing, $alsoOnTime] = Student::factory()->count(4)->for($this->schoolClass)->create();

    AssignmentSubmission::factory()->create(['assignment_id' => $assignment->id, 'student_id' => $onTime->id, 'submitted_at' => now()->subDays(2)]);
    AssignmentSubmission::factory()->create(['assignment_id' => $assignment->id, 'student_id' => $alsoOnTime->id, 'submitted_at' => now()->subDays(2)]);
    AssignmentSubmission::factory()->create(['assignment_id' => $assignment->id, 'student_id' => $late->id, 'submitted_at' => now()]);

    $response = $this->actingAs($this->teacher->user)->get(route('teacher.assignments.show', $assignment));

    $response->assertOk()
        ->assertViewHas('summary', ['students' => 4, 'submitted' => 3, 'late' => 1, 'not_submitted' => 1])
        ->assertSee('75%');

    $statuses = $response->viewData('rows')->mapWithKeys(fn ($row) => [$row['student']->id => $row['status']->value]);

    expect($statuses[$onTime->id])->toBe('submitted')
        ->and($statuses[$late->id])->toBe('late')
        ->and($statuses[$missing->id])->toBe('not_submitted');

    expect($assignment->submissionSummary()['rate'])->toBe(75);
});

test('a teacher can record a student submission with a file', function () {
    Notification::fake();
    $assignment = Assignment::factory()->for($this->teacher)->create(['school_class_id' => $this->schoolClass->id, 'subject_id' => $this->subject->id]);
    $student = Student::factory()->for($this->schoolClass)->create();

    $this->actingAs($this->teacher->user)
        ->post(route('teacher.submissions.store', $assignment), [
            'student_id' => $student->id,
            'submitted_at' => now()->format('Y-m-d\TH:i'),
            'file' => UploadedFile::fake()->create('work.jpg', 120, 'image/jpeg'),
        ])
        ->assertSessionHas('success', 'Submission recorded.');

    $submission = AssignmentSubmission::sole();
    Storage::disk('local')->assertExists($submission->file_path);

    // The teacher recorded it themselves, so they are not notified about it.
    Notification::assertNothingSent();

    $this->get(route('teacher.submissions.download', $submission))->assertDownload('work.jpg');
});

test('a submission arriving from someone else notifies the teacher', function () {
    Notification::fake();
    $assignment = Assignment::factory()->for($this->teacher)->create(['school_class_id' => $this->schoolClass->id]);

    AssignmentSubmission::factory()->create(['assignment_id' => $assignment->id, 'student_id' => Student::factory()->for($this->schoolClass)]);

    Notification::assertSentTo($this->teacher->user, SubmissionReceived::class);
});

test('a student cannot have two submissions or be from another class', function () {
    $assignment = Assignment::factory()->for($this->teacher)->create(['school_class_id' => $this->schoolClass->id]);
    $student = Student::factory()->for($this->schoolClass)->create();
    AssignmentSubmission::factory()->create(['assignment_id' => $assignment->id, 'student_id' => $student->id]);

    $this->actingAs($this->teacher->user)
        ->post(route('teacher.submissions.store', $assignment), ['student_id' => $student->id, 'submitted_at' => now()->format('Y-m-d\TH:i')])
        ->assertSessionHasErrors(['student_id' => 'This student already has a submission recorded.']);

    $this->post(route('teacher.submissions.store', $assignment), ['student_id' => Student::factory()->create()->id, 'submitted_at' => now()->format('Y-m-d\TH:i')])
        ->assertSessionHasErrors(['student_id' => 'Please choose a student from this class.']);
});

test('another teacher cannot record submissions on my assignment', function () {
    $assignment = Assignment::factory()->for($this->teacher)->create(['school_class_id' => $this->schoolClass->id]);
    $student = Student::factory()->for($this->schoolClass)->create();

    $this->actingAs(teacherTeaching()->user)
        ->post(route('teacher.submissions.store', $assignment), ['student_id' => $student->id, 'submitted_at' => now()->format('Y-m-d\TH:i')])
        ->assertForbidden();
});

test('the deadline reminder command notifies teachers about assignments due soon', function () {
    Notification::fake();
    Student::factory()->count(2)->for($this->schoolClass)->create();
    Assignment::factory()->for($this->teacher)->create(['school_class_id' => $this->schoolClass->id, 'due_at' => now()->addHours(10)]);
    Assignment::factory()->for($this->teacher)->create(['school_class_id' => $this->schoolClass->id, 'due_at' => now()->addDays(5)]);

    $this->artisan('assignments:send-deadline-reminders')->expectsOutput('Sent 1 deadline reminder(s).')->assertSuccessful();

    Notification::assertSentToTimes($this->teacher->user, AssignmentDeadlineApproaching::class, 1);
});
