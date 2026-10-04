<?php

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Notifications\AnnouncementPublished;
use Illuminate\Support\Facades\Notification;

test('an admin can publish an announcement and teachers are notified', function () {
    Notification::fake();
    $admin = adminUser();
    $teacher = teacherTeaching();

    $this->actingAs($admin)
        ->post(route('admin.announcements.store'), [
            'title' => 'Staff Meeting',
            'body' => 'Meeting on Friday at 2:30 PM.',
            'audience' => 'teachers',
            'status' => 'published',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Announcement published.');

    $announcement = Announcement::sole();

    expect($announcement->status)->toBe(AnnouncementStatus::Published)
        ->and($announcement->published_at)->not->toBeNull();

    Notification::assertSentTo($teacher->user, AnnouncementPublished::class);
    Notification::assertNotSentTo($admin, AnnouncementPublished::class);
});

test('a draft announcement is not shown to teachers or notified', function () {
    Notification::fake();
    $teacher = teacherTeaching();

    $this->actingAs(adminUser())->post(route('admin.announcements.store'), [
        'title' => 'Secret Draft',
        'body' => 'Not ready yet.',
        'audience' => 'all',
        'status' => 'draft',
    ]);

    Notification::assertNothingSent();

    $this->actingAs($teacher->user)->get(route('teacher.announcements.index'))->assertDontSee('Secret Draft');
    $this->get(route('teacher.announcements.show', Announcement::sole()))->assertNotFound();
});

test('an admin can publish a draft later', function () {
    Notification::fake();
    $teacher = teacherTeaching();
    $draft = Announcement::factory()->draft()->create();

    $this->actingAs(adminUser())->patch(route('admin.announcements.publish', $draft))->assertRedirect();

    expect($draft->fresh()->isPublished())->toBeTrue();
    Notification::assertSentTo($teacher->user, AnnouncementPublished::class);
});

test('an admin can edit and delete announcements', function () {
    $announcement = Announcement::factory()->create();

    $this->actingAs(adminUser())
        ->put(route('admin.announcements.update', $announcement), ['title' => 'New title', 'body' => 'Body', 'audience' => 'all', 'status' => 'published'])
        ->assertRedirect(route('admin.announcements.show', $announcement));

    expect($announcement->fresh()->title)->toBe('New title');

    $this->delete(route('admin.announcements.destroy', $announcement))->assertRedirect(route('admin.announcements.index'));
    $this->assertModelMissing($announcement);
});

test('announcements require a title, description and valid audience', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.announcements.store'), ['audience' => 'everyone', 'status' => 'published'])
        ->assertSessionHasErrors(['title', 'body', 'audience']);
});

test('a teacher can read and mark an announcement as read', function () {
    $teacher = teacherTeaching();
    $announcement = Announcement::factory()->create(['title' => 'Sports Day']);

    $this->actingAs($teacher->user)->get(route('teacher.announcements.show', $announcement))->assertOk()->assertSee('Mark as read');

    $this->post(route('teacher.announcements.read', $announcement))->assertSessionHas('success');

    expect($announcement->isReadBy($teacher->user))->toBeTrue();
    $this->get(route('teacher.announcements.show', $announcement))->assertSee('You have read this announcement.');
});

test('teachers do not see admin-only announcements', function () {
    Announcement::factory()->create(['title' => 'Admins Only Notice', 'audience' => 'admins']);
    Announcement::factory()->create(['title' => 'For Everyone']);

    $this->actingAs(teacherTeaching()->user)
        ->get(route('teacher.announcements.index'))
        ->assertSee('For Everyone')
        ->assertDontSee('Admins Only Notice');
});

test('opening a notification marks it read and goes to its page', function () {
    $teacher = teacherTeaching();
    $announcement = Announcement::factory()->create();
    $teacher->user->notify(new AnnouncementPublished($announcement));
    $notification = $teacher->user->notifications()->sole();

    $this->actingAs($teacher->user)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('teacher.announcements.show', $announcement));

    expect($notification->fresh()->read_at)->not->toBeNull();
});
