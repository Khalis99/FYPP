<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Notifications\AssignmentDeadlineApproaching;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('assignments:send-deadline-reminders')]
#[Description('Notify teachers about assignments due in the next 24 hours that still have missing submissions')]
class SendAssignmentDeadlineReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $assignments = Assignment::query()
            ->withSubmissionCounts()
            ->with('teacher.user')
            ->whereBetween('due_at', [now(), now()->addDay()])
            ->get();

        $sent = 0;

        foreach ($assignments as $assignment) {
            $missing = $assignment->submissionSummary()['not_submitted'];

            if ($missing > 0) {
                $assignment->teacher->user->notify(new AssignmentDeadlineApproaching($assignment, $missing));
                $sent++;
            }
        }

        $this->info("Sent {$sent} deadline reminder(s).");

        return self::SUCCESS;
    }
}
