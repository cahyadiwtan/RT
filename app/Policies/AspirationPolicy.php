<?php

namespace App\Policies;

use App\Models\Aspiration;
use App\Models\User;

class AspirationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isPengurus();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Aspiration $aspiration): bool
    {
        if ($user->isPengurus()) {
            return true;
        }

        return $user->isWarga() && $user->resident_id === $aspiration->resident_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->resident_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Aspiration $aspiration): bool
    {
        return $user->isPengurus();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Aspiration $aspiration): bool
    {
        return false;
    }
}
