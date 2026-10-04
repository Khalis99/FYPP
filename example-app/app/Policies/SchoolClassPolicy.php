<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;

/**
 * Teachers can only open classes they are assigned to.
 */
class SchoolClassPolicy
{
    public function view(User $user, SchoolClass $schoolClass): bool
    {
        return (bool) $user->teacher?->teachesClass($schoolClass->id);
    }
}
