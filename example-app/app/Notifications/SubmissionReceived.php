<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the teacher when a submission arrives from someone else
 * (for example, from the future student portal).
 */
class SubmissionReceived extends Notification
{
    use Queueable;

    public function __construct(public AssignmentSubmission $submission) {}

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
            'title' => 'New submission',
            'message' => "{$this->submission->student->name} submitted \"{$this->submission->assignment->title}\".",
            'url' => route('teacher.assignments.show', $this->submission->assignment_id),
        ];
    }
}
