<?php

namespace App\Policies;

use App\Models\TrackingStage;
use App\Models\User;

/** Who may do what with TrackingStage records in the admin panel (see App\Support\Roles). */
class TrackingStagePolicy
{
    private const VIEW = ['admin'];

    private const EDIT = ['admin'];

    private const DELETE = ['admin'];

    private const CREATE = ['admin'];

    public function viewAny(User $user): bool
    {
        return $user->hasRole(...self::VIEW);
    }

    public function view(User $user, TrackingStage $record): bool
    {
        return $user->hasRole(...self::VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...self::CREATE);
    }

    public function update(User $user, TrackingStage $record): bool
    {
        return $user->hasRole(...self::EDIT);
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole(...self::EDIT);
    }

    public function delete(User $user, TrackingStage $record): bool
    {
        return $user->hasRole(...self::DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(...self::DELETE);
    }
}
