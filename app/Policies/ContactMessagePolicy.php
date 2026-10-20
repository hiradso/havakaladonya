<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

/** Who may do what with ContactMessage records in the admin panel (see App\Support\Roles). */
class ContactMessagePolicy
{
    private const VIEW = ['admin', 'sales_ops'];

    private const EDIT = ['admin', 'sales_ops'];

    private const DELETE = ['admin'];

    private const CREATE = []; // records only come from the website

    public function viewAny(User $user): bool
    {
        return $user->hasRole(...self::VIEW);
    }

    public function view(User $user, ContactMessage $record): bool
    {
        return $user->hasRole(...self::VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...self::CREATE);
    }

    public function update(User $user, ContactMessage $record): bool
    {
        return $user->hasRole(...self::EDIT);
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole(...self::EDIT);
    }

    public function delete(User $user, ContactMessage $record): bool
    {
        return $user->hasRole(...self::DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(...self::DELETE);
    }
}
