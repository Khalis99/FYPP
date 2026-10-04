<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('a teacher can view their profile with teacher details and subjects', function () {
    $teacher = teacherTeaching();

    $this->actingAs($teacher->user)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee($teacher->teacher_code)
        ->assertSee($teacher->teachingLoads()->first()->subject->name);
});

test('a user can update their profile and upload a photo', function () {
    Storage::fake('public');
    $teacher = teacherTeaching();

    $this->actingAs($teacher->user)
        ->put(route('profile.update'), [
            'name' => 'Cikgu Aisyah',
            'email' => 'new-email@school.test',
            'phone' => '012-9998888',
            'photo' => UploadedFile::fake()->create('me.jpg', 120, 'image/jpeg'),
        ])
        ->assertRedirect(route('profile.show'));

    $user = $teacher->user->fresh();

    expect($user->name)->toBe('Cikgu Aisyah')
        ->and($user->email)->toBe('new-email@school.test')
        ->and($user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_photo_path);
});

test('profile photos must be images', function () {
    $teacher = teacherTeaching();

    $this->actingAs($teacher->user)
        ->put(route('profile.update'), ['name' => 'X', 'email' => $teacher->user->email, 'photo' => UploadedFile::fake()->create('doc.pdf', 10)])
        ->assertSessionHasErrors('photo');
});

test('a user can change their password', function () {
    $teacher = teacherTeaching();

    $this->actingAs($teacher->user)
        ->put(route('profile.password'), ['current_password' => 'password', 'password' => 'newPass123', 'password_confirmation' => 'newPass123'])
        ->assertSessionHas('success');

    expect(Hash::check('newPass123', $teacher->user->fresh()->password))->toBeTrue();
});

test('changing password requires the correct current password and a strong new one', function () {
    $teacher = teacherTeaching();

    $this->actingAs($teacher->user)
        ->put(route('profile.password'), ['current_password' => 'wrong', 'password' => 'short', 'password_confirmation' => 'short'])
        ->assertSessionHasErrorsIn('password', ['current_password', 'password']);
});
