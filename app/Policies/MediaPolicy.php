<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

/** Media library: the general manager and writers (they need pictures for articles). */
class MediaPolicy
{
    private const ROLES = ['admin', 'writer'];

    public function viewAny(User $user): bool
    {
        return $user->hasRole(...self::ROLES);
    }

    public function view(User $user, Media $record): bool
    {
        return $user->hasRole(...self::ROLES);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...self::ROLES);
    }

    public function update(User $user, Media $record): bool
    {
        return $user->hasRole(...self::ROLES);
    }

    /** A picture that is used somewhere on the site cannot be deleted. */
    public function delete(User $user, Media $record): bool
    {
        return $user->hasRole(...self::ROLES) && $record->usages() === [];
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(...self::ROLES);
    }
}
