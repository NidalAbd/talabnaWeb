<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class BanPolicy
{
    /**
     * Determine whether the user can ban other users.
     */
    public function banUser(User $user): bool
    {
        // Check if the user has admin role/permission
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can unban other users.
     */
    public function unbanUser(User $user): bool
    {
        // Check if the user has admin role/permission
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can ban devices.
     */
    public function banDevice(User $user): bool
    {
        // Check if the user has admin role/permission
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can unban devices.
     */
    public function unbanDevice(User $user): bool
    {
        // Check if the user has admin role/permission
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view ban history.
     */
    public function viewBanHistory(User $user): bool
    {
        // Check if the user has admin role/permission
        return $user->hasRole('admin');
    }
}
