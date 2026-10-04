<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

/**
 * Teachers manage only the assignments they created.
 */
class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        return $user->teacher?->id === $assignment->teacher_id;
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->view($user, $assignment);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->view($user, $assignment);
    }
}
