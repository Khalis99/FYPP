<?php

namespace App\Policies;

use App\Models\RphDocument;
use App\Models\User;

/**
 * An RPH is private to the teacher who wrote it.
 */
class RphDocumentPolicy
{
    public function view(User $user, RphDocument $rphDocument): bool
    {
        return $user->teacher?->id === $rphDocument->teacher_id;
    }

    public function update(User $user, RphDocument $rphDocument): bool
    {
        return $this->view($user, $rphDocument);
    }

    public function delete(User $user, RphDocument $rphDocument): bool
    {
        return $this->view($user, $rphDocument);
    }
}
