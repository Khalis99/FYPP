<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teacher;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route(auth()->user()->role->homeRoute())
    : redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Guest (login and password reset)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->name('password.email')->middleware('throttle:6,1');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Any signed-in user
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| Teacher portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('dashboard', Teacher\DashboardController::class)->name('dashboard');

    Route::get('classes', [Teacher\ClassController::class, 'index'])->name('classes.index');
    Route::get('classes/{schoolClass}', [Teacher\ClassController::class, 'show'])->name('classes.show');

    Route::get('materials/{material}/download', [Teacher\TeachingMaterialController::class, 'download'])->name('materials.download');
    Route::resource('materials', Teacher\TeachingMaterialController::class)
        ->parameters(['materials' => 'material']);

    Route::get('assignments/{assignment}/attachment', [Teacher\AssignmentController::class, 'attachment'])->name('assignments.attachment');
    Route::resource('assignments', Teacher\AssignmentController::class);

    Route::post('assignments/{assignment}/submissions', [Teacher\SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('submissions/{submission}/download', [Teacher\SubmissionController::class, 'download'])->name('submissions.download');
    Route::delete('submissions/{submission}', [Teacher\SubmissionController::class, 'destroy'])->name('submissions.destroy');

    Route::get('students', [Teacher\StudentController::class, 'index'])->name('students.index');
    Route::get('students/{student}', [Teacher\StudentController::class, 'show'])->name('students.show');

    Route::get('timetable', Teacher\TimetableController::class)->name('timetable.index');

    Route::get('announcements', [Teacher\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/{announcement}', [Teacher\AnnouncementController::class, 'show'])->name('announcements.show');
    Route::post('announcements/{announcement}/read', [Teacher\AnnouncementController::class, 'markAsRead'])->name('announcements.read');

    Route::get('rph/generator', [Teacher\RphController::class, 'create'])->name('rph.create');
    Route::post('rph/generate', [Teacher\RphController::class, 'generate'])->name('rph.generate')->middleware('throttle:10,1');
    Route::get('rph/draft', [Teacher\RphController::class, 'draft'])->name('rph.draft');
    Route::get('rph/{rph}/print', [Teacher\RphController::class, 'print'])->name('rph.print');
    Route::get('rph/{rph}/download', [Teacher\RphController::class, 'download'])->name('rph.download');
    Route::resource('rph', Teacher\RphController::class)->except(['create'])
        ->parameters(['rph' => 'rph']);
});

/*
|--------------------------------------------------------------------------
| Admin portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', Admin\DashboardController::class)->name('dashboard');

    Route::patch('users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');
    Route::resource('users', Admin\UserController::class)->except(['show', 'destroy']);

    Route::resource('teachers', Admin\TeacherController::class)->except(['destroy']);

    Route::resource('students', Admin\StudentController::class);

    Route::post('classes/{schoolClass}/teachers', [Admin\ClassTeacherController::class, 'store'])->name('classes.teachers.store');
    Route::delete('classes/{schoolClass}/teachers/{classTeacher}', [Admin\ClassTeacherController::class, 'destroy'])->name('classes.teachers.destroy');
    Route::post('classes/{schoolClass}/students', [Admin\ClassController::class, 'addStudents'])->name('classes.students.store');
    Route::delete('classes/{schoolClass}/students/{student}', [Admin\ClassController::class, 'removeStudent'])->name('classes.students.destroy')->scopeBindings();
    Route::resource('classes', Admin\ClassController::class)->parameters(['classes' => 'schoolClass']);

    Route::resource('subjects', Admin\SubjectController::class)->except(['show', 'create', 'edit']);

    Route::patch('announcements/{announcement}/publish', [Admin\AnnouncementController::class, 'publish'])->name('announcements.publish');
    Route::resource('announcements', Admin\AnnouncementController::class);

    Route::resource('timetable', Admin\TimetableController::class)->except(['show'])
        ->parameters(['timetable' => 'timetable']);
});
