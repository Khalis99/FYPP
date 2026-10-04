<?php

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeachingMaterial;
use App\Models\Timetable;
use Illuminate\Support\Carbon;

test('the teacher dashboard shows statistics, schedule, assignments and announcements', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-05 07:00')); // a Monday

    $class = SchoolClass::factory()->create(['name' => '1 Amanah']);
    $subject = Subject::factory()->create(['name' => 'Science']);
    $teacher = teacherTeaching($class, $subject);
    $students = Student::factory()->count(3)->for($class)->create();

    TeachingMaterial::factory()->for($teacher)->create(['subject_id' => $subject->id, 'school_class_id' => $class->id]);
    $assignment = Assignment::factory()->for($teacher)->create([
        'title' => 'Photosynthesis Worksheet',
        'subject_id' => $subject->id,
        'school_class_id' => $class->id,
        'due_at' => now()->addDays(2),
    ]);
    AssignmentSubmission::factory()->create(['assignment_id' => $assignment->id, 'student_id' => $students[0]->id]);
    Timetable::factory()->create(['teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'school_class_id' => $class->id, 'day' => 1, 'room' => 'Science Lab 1']);
    Announcement::factory()->create(['title' => 'Staff Meeting This Friday']);

    $this->actingAs($teacher->user)
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertSee('Welcome back, '.$teacher->user->name, false)
        ->assertViewHas('stats', ['classes' => 1, 'materials' => 1, 'activeAssignments' => 1, 'pendingSubmissions' => 2])
        ->assertSee('Photosynthesis Worksheet')
        ->assertSee('Science Lab 1')
        ->assertSee('Staff Meeting This Friday');
});

test('the dashboard shows a weekend message on Saturday and Sunday', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-04 09:00')); // a Sunday

    $this->actingAs(teacherTeaching()->user)
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertSee('No classes today');
});

test('the admin dashboard shows school statistics', function () {
    teacherTeaching();
    Student::factory()->count(4)->create();

    $this->actingAs(adminUser())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('stats', fn (array $stats) => $stats['teachers'] === 1 && $stats['students'] === 4);
});
