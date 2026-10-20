<?php

namespace App\Policies;

use App\Models\User;

/** Only the general manager manages staff accounts, and can't lock themselves out. */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $record): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $record): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $record): bool
    {
        return $user->isAdmin() && $user->isNot($record);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
