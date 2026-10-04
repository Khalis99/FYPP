<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

/**
 * Teachers can only view students in classes they teach.
 */
class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        return $student->school_class_id !== null
            && (bool) $user->teacher?->teachesClass($student->school_class_id);
    }
}
