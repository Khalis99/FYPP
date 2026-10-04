<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssignmentDeadlineApproaching extends Notification
{
    use Queueable;

    public function __construct(public Assignment $assignment, public int $notSubmittedCount) {}

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
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Assignment due soon',
            'message' => "\"{$this->assignment->title}\" is due {$this->assignment->due_at->format('j M, g:i A')}. {$this->notSubmittedCount} student(s) have not submitted.",
            'url' => route('teacher.assignments.show', $this->assignment),
        ];
    }
}
