<?php

namespace App\Notifications;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AnnouncementPublished extends Notification
{
    use Queueable;

    public function __construct(public Announcement $announcement) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, message: string, url: string}
     */
    public function toArray(User $notifiable): array
    {
        return [
            'title' => 'New announcement',
            'message' => Str::limit($this->announcement->title, 80),
            'url' => $notifiable->isAdmin()
                ? route('admin.announcements.show', $this->announcement)
                : route('teacher.announcements.show', $this->announcement),
        ];
    }
}
