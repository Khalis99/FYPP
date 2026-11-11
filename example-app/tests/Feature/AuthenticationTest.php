<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('the login page can be displayed', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Log in to Pelita')
        ->assertSee('Staff ID')
        ->assertSee('name="staff_id"', false)
        ->assertDontSee('name="email"', false);
});

test('guests are redirected to the login page', function () {
    $this->get(route('teacher.dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('a teacher is sent to the teacher dashboard after login', function () {
    $teacher = teacherTeaching();

    $this->post(route('login.store'), ['staff_id' => $teacher->user->staff_id, 'password' => 'password'])
        ->assertRedirect(route('teacher.dashboard'));

    $this->assertAuthenticatedAs($teacher->user);
});

test('an admin is sent to the admin dashboard after login', function () {
    $admin = adminUser();

    $this->post(route('login.store'), ['staff_id' => $admin->staff_id, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('login fails with a wrong password', function () {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), ['staff_id' => $user->staff_id, 'password' => 'wrong-password'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['staff_id' => 'The staff ID or password you entered is incorrect.']);

    $this->assertGuest();
});

test('login fails for a staff ID that does not exist', function () {
    $this->post(route('login.store'), ['staff_id' => '123456', 'password' => 'password'])
        ->assertSessionHasErrors(['staff_id' => 'The staff ID or password you entered is incorrect.']);

    $this->assertGuest();
});

test('login requires a six-digit staff ID and a password', function () {
    $this->post(route('login.store'), ['staff_id' => '', 'password' => ''])
        ->assertSessionHasErrors(['staff_id' => 'Please enter your staff ID.', 'password']);

    foreach (['12345', '1234567', 'abcdef', '26 001'] as $invalid) {
        $this->post(route('login.store'), ['staff_id' => $invalid, 'password' => 'password'])
            ->assertSessionHasErrors(['staff_id' => 'Your staff ID is 6 digits, for example 260001.']);
    }

    $this->assertGuest();
});

test('an email address can no longer be used to log in', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('staff_id');

    $this->post(route('login.store'), ['staff_id' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('staff_id');

    $this->assertGuest();
});

test('a deactivated user cannot log in', function () {
    $user = User::factory()->inactive()->create();

    $this->post(route('login.store'), ['staff_id' => $user->staff_id, 'password' => 'password'])
        ->assertSessionHasErrors('staff_id');

    $this->assertGuest();
});

test('a user deactivated while logged in is signed out', function () {
    $teacher = teacherTeaching();
    $this->actingAs($teacher->user);
    $teacher->user->update(['is_active' => false]);

    $this->get(route('teacher.dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('login is rate limited after too many attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), ['staff_id' => $user->staff_id, 'password' => 'wrong']);
    }

    $this->post(route('login.store'), ['staff_id' => $user->staff_id, 'password' => 'password'])
        ->assertSessionHasErrors(['staff_id']);

    $this->assertGuest();
});

test('users can log out', function () {
    $this->actingAs(adminUser())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('a password reset link can still be requested by email', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});
