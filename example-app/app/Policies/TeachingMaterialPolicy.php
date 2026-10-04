<?php

namespace App\Policies;

use App\Models\TeachingMaterial;
use App\Models\User;

/**
 * Teachers manage only the materials they uploaded.
 */
class TeachingMaterialPolicy
{
    public function view(User $user, TeachingMaterial $teachingMaterial): bool
    {
        return $user->teacher?->id === $teachingMaterial->teacher_id;
    }

    public function update(User $user, TeachingMaterial $teachingMaterial): bool
    {
        return $this->view($user, $teachingMaterial);
    }

    public function delete(User $user, TeachingMaterial $teachingMaterial): bool
    {
        return $this->view($user, $teachingMaterial);
    }
}
