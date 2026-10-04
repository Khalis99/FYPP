<?php

use App\Models\Assignment;
use App\Models\RphDocument;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeachingMaterial;

test('a teacher cannot access admin pages', function (string $routeName) {
    $this->actingAs(teacherTeaching()->user)
        ->get(route($routeName))
        ->assertForbidden()
        ->assertSee('Sorry, you do not have permission to access this page.');
})->with(['admin.dashboard', 'admin.users.index', 'admin.teachers.index', 'admin.students.index', 'admin.classes.index', 'admin.announcements.index', 'admin.timetable.index']);

test('an admin cannot access teacher-only pages', function (string $routeName) {
    $this->actingAs(adminUser())->get(route($routeName))->assertForbidden();
})->with(['teacher.dashboard', 'teacher.materials.index', 'teacher.assignments.index', 'teacher.rph.create']);

test('a teacher cannot open a class they are not assigned to', function () {
    $teacher = teacherTeaching();
    $otherClass = SchoolClass::factory()->create();

    $this->actingAs($teacher->user)->get(route('teacher.classes.show', $otherClass))->assertForbidden();
});

test('a teacher can open a class they are assigned to', function () {
    $class = SchoolClass::factory()->create();
    $teacher = teacherTeaching($class);

    $this->actingAs($teacher->user)->get(route('teacher.classes.show', $class))->assertOk()->assertSee($class->name);
});

test('a teacher cannot view students from other classes', function () {
    $teacher = teacherTeaching();
    $student = Student::factory()->create();

    $this->actingAs($teacher->user)->get(route('teacher.students.show', $student))->assertForbidden();
});

test('student records only list students from the teacher\'s classes', function () {
    $class = SchoolClass::factory()->create();
    $teacher = teacherTeaching($class);
    $mine = Student::factory()->for($class)->create();
    $other = Student::factory()->create();

    $this->actingAs($teacher->user)->get(route('teacher.students.index'))
        ->assertOk()
        ->assertSee($mine->name)
        ->assertDontSee($other->name);
});

test('a teacher cannot access another teacher\'s records', function () {
    $owner = teacherTeaching();
    $intruder = teacherTeaching();

    $material = TeachingMaterial::factory()->for($owner)->create();
    $assignment = Assignment::factory()->for($owner)->create();
    $rph = RphDocument::factory()->for($owner)->create();

    $this->actingAs($intruder->user);

    $this->get(route('teacher.materials.show', $material))->assertForbidden();
    $this->get(route('teacher.materials.download', $material))->assertForbidden();
    $this->delete(route('teacher.materials.destroy', $material))->assertForbidden();
    $this->get(route('teacher.assignments.show', $assignment))->assertForbidden();
    $this->delete(route('teacher.assignments.destroy', $assignment))->assertForbidden();
    $this->get(route('teacher.rph.show', $rph))->assertForbidden();
    $this->get(route('teacher.rph.print', $rph))->assertForbidden();
    $this->delete(route('teacher.rph.destroy', $rph))->assertForbidden();

    expect(TeachingMaterial::count())->toBe(1)
        ->and(Assignment::count())->toBe(1)
        ->and(RphDocument::count())->toBe(1);
});
