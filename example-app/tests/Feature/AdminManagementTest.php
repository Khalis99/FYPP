<?php

use App\Enums\UserRole;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

test('an admin can create a teacher account with a teacher profile', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.teachers.store'), [
            'name' => 'Nur Aisyah',
            'email' => 'aisyah@school.test',
            'phone' => '012-3456789',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'teacher_code' => 'TCH9001',
            'department' => 'Science',
        ])
        ->assertRedirect();

    $teacher = Teacher::where('teacher_code', 'TCH9001')->sole();

    expect($teacher->user->email)->toBe('aisyah@school.test')
        ->and($teacher->user->role)->toBe(UserRole::Teacher);

    $this->post(route('logout'));
    $this->post(route('login.store'), ['email' => 'aisyah@school.test', 'password' => 'secret123'])->assertRedirect(route('teacher.dashboard'));
});

test('teacher creation validates unique email and teacher ID', function () {
    $existing = teacherTeaching();

    $this->actingAs(adminUser())
        ->post(route('admin.teachers.store'), [
            'name' => 'Copy',
            'email' => $existing->user->email,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'teacher_code' => $existing->teacher_code,
        ])
        ->assertSessionHasErrors(['email', 'teacher_code']);
});

test('an admin can deactivate and reactivate a teacher', function () {
    $teacher = teacherTeaching();

    $this->actingAs(adminUser())->patch(route('admin.users.toggle', $teacher->user))->assertSessionHas('success');
    expect($teacher->user->fresh()->is_active)->toBeFalse();

    $this->patch(route('admin.users.toggle', $teacher->user));
    expect($teacher->user->fresh()->is_active)->toBeTrue();
});

test('an admin cannot deactivate their own account', function () {
    $admin = adminUser();

    $this->actingAs($admin)->patch(route('admin.users.toggle', $admin))->assertSessionHas('error');

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('an admin can create another administrator account', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.users.store'), [
            'name' => 'Second Admin',
            'email' => 'admin2@school.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect(User::where('email', 'admin2@school.test')->sole()->isAdmin())->toBeTrue();
});

test('an admin can create a class and assign a teacher at the same time', function () {
    $subject = Subject::factory()->create();
    $teacher = Teacher::factory()->create();

    $this->actingAs(adminUser())
        ->post(route('admin.classes.store'), [
            'name' => '1 Amanah',
            'form_level' => 1,
            'academic_year' => '2026',
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ])
        ->assertRedirect();

    $class = SchoolClass::where('name', '1 Amanah')->sole();

    expect($teacher->teachesSubjectInClass($subject->id, $class->id))->toBeTrue();
});

test('class names must be unique within an academic year', function () {
    SchoolClass::factory()->create(['name' => '1 Amanah', 'academic_year' => '2026']);

    $this->actingAs(adminUser())
        ->post(route('admin.classes.store'), ['name' => '1 Amanah', 'form_level' => 1, 'academic_year' => '2026'])
        ->assertSessionHasErrors('name');

    $this->post(route('admin.classes.store'), ['name' => '1 Amanah', 'form_level' => 1, 'academic_year' => '2027'])
        ->assertSessionDoesntHaveErrors();
});

test('an admin can edit and delete a class', function () {
    $class = SchoolClass::factory()->create();
    $student = Student::factory()->for($class)->create();

    $this->actingAs(adminUser())
        ->put(route('admin.classes.update', $class), ['name' => '3 Cekal', 'form_level' => 3, 'academic_year' => '2026'])
        ->assertRedirect(route('admin.classes.show', $class));
    expect($class->fresh()->name)->toBe('3 Cekal');

    $this->delete(route('admin.classes.destroy', $class))->assertRedirect(route('admin.classes.index'));
    $this->assertModelMissing($class);
    expect($student->fresh()->school_class_id)->toBeNull();
});

test('assigning a teacher to a subject that already has one replaces them', function () {
    $class = SchoolClass::factory()->create();
    $subject = Subject::factory()->create();
    $oldTeacher = teacherTeaching($class, $subject);
    $newTeacher = Teacher::factory()->create();

    $this->actingAs(adminUser())
        ->post(route('admin.classes.teachers.store', $class), ['subject_id' => $subject->id, 'teacher_id' => $newTeacher->id])
        ->assertSessionHas('success');

    expect(ClassTeacher::where('school_class_id', $class->id)->sole()->teacher_id)->toBe($newTeacher->id)
        ->and($oldTeacher->teachesClass($class->id))->toBeFalse();
});

test('an admin can remove a teacher from a class subject', function () {
    $class = SchoolClass::factory()->create();
    $teacher = teacherTeaching($class);
    $load = $teacher->teachingLoads()->sole();

    $this->actingAs(adminUser())->delete(route('admin.classes.teachers.destroy', [$class, $load]))->assertSessionHas('success');

    $this->assertModelMissing($load);
});

test('an admin can add students to a class and remove them', function () {
    $class = SchoolClass::factory()->create();
    $students = Student::factory()->count(2)->create();

    $this->actingAs(adminUser())
        ->post(route('admin.classes.students.store', $class), ['student_ids' => $students->pluck('id')->all()])
        ->assertSessionHas('success', "2 student(s) added to {$class->name}.");

    expect($class->students()->count())->toBe(2);

    $this->delete(route('admin.classes.students.destroy', [$class, $students[0]]));
    expect($students[0]->fresh()->school_class_id)->toBeNull();
});

test('an admin can manage student records', function () {
    $class = SchoolClass::factory()->create();

    $this->actingAs(adminUser())
        ->post(route('admin.students.store'), ['name' => 'Ali bin Abu', 'student_code' => 'S20269999', 'email' => 'ali@student.test', 'school_class_id' => $class->id, 'is_active' => '1'])
        ->assertRedirect();

    $student = Student::where('student_code', 'S20269999')->sole();

    $this->put(route('admin.students.update', $student), ['name' => 'Ali Abu', 'student_code' => 'S20269999', 'is_active' => '0'])
        ->assertRedirect(route('admin.students.show', $student));
    expect($student->fresh()->name)->toBe('Ali Abu')->and($student->fresh()->is_active)->toBeFalse();

    $this->delete(route('admin.students.destroy', $student))->assertRedirect(route('admin.students.index'));
    $this->assertModelMissing($student);
});

test('a subject that is assigned to classes cannot be deleted', function () {
    $subject = Subject::factory()->create();
    teacherTeaching(subject: $subject);

    $this->actingAs(adminUser())->delete(route('admin.subjects.destroy', $subject))->assertSessionHas('error');

    $this->assertModelExists($subject);
});

test('admin list pages load', function (string $routeName) {
    teacherTeaching();
    Student::factory()->create();

    $this->actingAs(adminUser())->get(route($routeName))->assertOk();
})->with(['admin.users.index', 'admin.teachers.index', 'admin.students.index', 'admin.classes.index', 'admin.subjects.index', 'admin.announcements.index', 'admin.timetable.index', 'admin.teachers.create', 'admin.classes.create', 'admin.timetable.create']);
