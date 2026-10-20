<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/** Who may do what with Post records in the admin panel (see App\Support\Roles). */
class PostPolicy
{
    private const VIEW = ['admin', 'writer'];

    private const EDIT = ['admin', 'writer'];

    private const DELETE = ['admin', 'writer'];

    private const CREATE = ['admin', 'writer'];

    public function viewAny(User $user): bool
    {
        return $user->hasRole(...self::VIEW);
    }

    public function view(User $user, Post $record): bool
    {
        return $user->hasRole(...self::VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...self::CREATE);
    }

    public function update(User $user, Post $record): bool
    {
        return $user->hasRole(...self::EDIT);
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole(...self::EDIT);
    }

    public function delete(User $user, Post $record): bool
    {
        return $user->hasRole(...self::DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(...self::DELETE);
    }
}
