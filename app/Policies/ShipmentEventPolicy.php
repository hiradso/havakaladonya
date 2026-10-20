<?php

namespace App\Policies;

use App\Models\ShipmentEvent;
use App\Models\User;

/** Who may do what with ShipmentEvent records in the admin panel (see App\Support\Roles). */
class ShipmentEventPolicy
{
    private const VIEW = ['admin', 'sales_ops', 'finance'];

    private const EDIT = ['admin', 'sales_ops'];

    private const DELETE = ['admin', 'sales_ops'];

    private const CREATE = ['admin', 'sales_ops'];

    public function viewAny(User $user): bool
    {
        return $user->hasRole(...self::VIEW);
    }

    public function view(User $user, ShipmentEvent $record): bool
    {
        return $user->hasRole(...self::VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(...self::CREATE);
    }

    public function update(User $user, ShipmentEvent $record): bool
    {
        return $user->hasRole(...self::EDIT);
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole(...self::EDIT);
    }

    public function delete(User $user, ShipmentEvent $record): bool
    {
        return $user->hasRole(...self::DELETE);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(...self::DELETE);
    }
}
