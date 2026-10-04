<?php

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\RphDocument;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeachingMaterial;
use App\Models\Timetable;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\Storage;

/**
 * Opens every page with the demo data loaded, to catch rendering errors.
 */
beforeEach(function () {
    Storage::fake('local');
    $this->seed(DemoDataSeeder::class);
});

test('every teacher page renders with demo data', function () {
    $user = User::where('email', 'aisyah@schoolhub.test')->sole();
    $teacher = $user->teacher;
    $class = $teacher->classesTaught()->first();

    $urls = [
        route('teacher.dashboard'),
        route('teacher.classes.index'),
        route('teacher.classes.show', $class),
        route('teacher.materials.index'),
        route('teacher.materials.create'),
        route('teacher.materials.show', $teacher->materials()->first()),
        route('teacher.materials.edit', $teacher->materials()->first()),
        route('teacher.assignments.index'),
        route('teacher.assignments.create'),
        route('teacher.assignments.show', $teacher->assignments()->first()),
        route('teacher.assignments.edit', $teacher->assignments()->first()),
        route('teacher.students.index'),
        route('teacher.students.show', $class->students()->first()),
        route('teacher.timetable.index'),
        route('teacher.announcements.index'),
        route('teacher.announcements.show', Announcement::published()->first()),
        route('teacher.rph.create'),
        route('teacher.rph.index'),
        route('teacher.rph.show', $teacher->rphDocuments()->first()),
        route('teacher.rph.edit', $teacher->rphDocuments()->first()),
        route('teacher.rph.print', $teacher->rphDocuments()->first()),
        route('profile.show'),
        route('profile.edit'),
    ];

    $this->actingAs($user);

    foreach ($urls as $url) {
        $this->get($url)->assertOk();
    }
});

test('every admin page renders with demo data', function () {
    $admin = User::where('email', 'admin@schoolhub.test')->sole();

    $urls = [
        route('admin.dashboard'),
        route('admin.users.index'),
        route('admin.users.create'),
        route('admin.users.edit', $admin),
        route('admin.teachers.index'),
        route('admin.teachers.show', Teacher::first()),
        route('admin.teachers.edit', Teacher::first()),
        route('admin.students.index'),
        route('admin.students.create'),
        route('admin.students.show', Student::first()),
        route('admin.students.edit', Student::first()),
        route('admin.classes.index'),
        route('admin.classes.create'),
        route('admin.classes.show', Student::first()->schoolClass),
        route('admin.classes.edit', Student::first()->schoolClass),
        route('admin.subjects.index'),
        route('admin.announcements.index'),
        route('admin.announcements.create'),
        route('admin.announcements.show', Announcement::first()),
        route('admin.announcements.edit', Announcement::first()),
        route('admin.timetable.index'),
        route('admin.timetable.create'),
        route('admin.timetable.edit', Timetable::first()),
        route('profile.show'),
    ];

    $this->actingAs($admin);

    foreach ($urls as $url) {
        $this->get($url)->assertOk();
    }
});

test('demo data contains the expected records', function () {
    expect(Teacher::count())->toBe(4)
        ->and(Student::count())->toBe(24)
        ->and(TeachingMaterial::count())->toBe(7)
        ->and(Assignment::count())->toBe(7)
        ->and(RphDocument::count())->toBe(4)
        ->and(Timetable::count())->toBe(80);

    Storage::disk('local')->assertExists(TeachingMaterial::first()->file_path);
});
