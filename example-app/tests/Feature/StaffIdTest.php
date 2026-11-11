<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

test('the first teacher ID of the year is the year followed by 0001', function () {
    Carbon::setTestNow(Carbon::parse('2027-03-01'));

    expect(User::nextStaffId(UserRole::Teacher))->toBe('270001');
});

test('the next teacher ID continues from the highest one this year', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-01'));
    User::factory()->create(['staff_id' => '260004']);
    User::factory()->create(['staff_id' => '260012']);
    User::factory()->create(['staff_id' => '250099']);

    expect(User::nextStaffId(UserRole::Teacher))->toBe('260013');
});

test('administrator IDs start with 9 and are numbered separately', function () {
    expect(User::nextStaffId(UserRole::Admin))->toBe('900001');

    User::factory()->admin()->create(['staff_id' => '900007']);

    expect(User::nextStaffId(UserRole::Admin))->toBe('900008')
        ->and(User::nextStaffId(UserRole::Teacher))->not->toStartWith('9');
});

test('the create forms suggest the next free staff ID', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-01'));
    User::factory()->create(['staff_id' => '260009']);
    $admin = User::factory()->admin()->create(['staff_id' => '900003']);

    $this->actingAs($admin)->get(route('admin.teachers.create'))
        ->assertOk()
        ->assertSee('value="260010"', false);

    $this->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('value="900004"', false);
});

test('an admin can change an account staff ID and the new one is used to log in', function () {
    $teacher = teacherTeaching();

    $this->actingAs(adminUser())
        ->put(route('admin.teachers.update', $teacher), [
            'name' => $teacher->user->name,
            'email' => $teacher->user->email,
            'staff_id' => '265555',
            'department' => 'Science',
        ])
        ->assertRedirect();

    expect($teacher->user->fresh()->staff_id)->toBe('265555');

    $this->post(route('logout'));
    $this->post(route('login.store'), ['staff_id' => '265555', 'password' => 'password'])
        ->assertRedirect(route('teacher.dashboard'));
});

test('a staff ID must be six digits and unique when an account is saved', function () {
    $existing = User::factory()->create(['staff_id' => '260100']);

    foreach (['12345', '1234567', 'abcdef', ''] as $invalid) {
        $this->actingAs(adminUser())
            ->post(route('admin.users.store'), [
                'name' => 'New Admin',
                'email' => 'new-admin@school.test',
                'staff_id' => $invalid,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors('staff_id');
    }

    $this->post(route('admin.users.store'), [
        'name' => 'New Admin',
        'email' => 'new-admin@school.test',
        'staff_id' => $existing->staff_id,
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertSessionHasErrors(['staff_id' => 'Another account already uses this staff ID.']);
});

test('the staff ID is shown on the profile page and in the admin lists', function () {
    $teacher = teacherTeaching();
    $admin = adminUser();

    $this->actingAs($admin)->get(route('profile.show'))->assertSee($admin->staff_id);

    $this->get(route('admin.users.index'))->assertSee($teacher->user->staff_id)->assertSee($admin->staff_id);
    $this->get(route('admin.teachers.index'))->assertSee($teacher->user->staff_id);
    $this->get(route('admin.teachers.index', ['search' => $teacher->user->staff_id]))->assertSee($teacher->user->name);
});

test('the demo accounts use six-digit staff IDs', function () {
    Storage::fake('local');
    $this->seed(DemoDataSeeder::class);

    expect(User::where('email', 'admin@schoolhub.test')->value('staff_id'))->toBe('900001')
        ->and(User::where('email', 'aisyah@schoolhub.test')->value('staff_id'))->toBe('260001')
        ->and(User::where('email', 'hakim@schoolhub.test')->value('staff_id'))->toBe('260002');

    $this->post(route('login.store'), ['staff_id' => '260001', 'password' => 'password'])
        ->assertRedirect(route('teacher.dashboard'));
});
