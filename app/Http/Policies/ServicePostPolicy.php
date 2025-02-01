<?php

namespace App\Policies;

use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePostPolicy
{
    /**
     * Determine whether the userController can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the userController can view the model.
     */
    public function view(User $user, ServicePost $servicePost): bool
    {
        //
    }

    /**
     * Determine whether the userController can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the userController can update the model.
     */
    public function update(User $user, ServicePost $servicePost): bool
    {
        //
    }

    /**
     * Determine whether the userController can delete the model.
     */
    public function delete(User $user, ServicePost $servicePost): bool
    {
        //
    }

    /**
     * Determine whether the userController can restore the model.
     */
    public function restore(User $user, ServicePost $servicePost): bool
    {
        //
    }

    /**
     * Determine whether the userController can permanently delete the model.
     */
    public function forceDelete(User $user, ServicePost $servicePost): bool
    {
        //
    }
}
