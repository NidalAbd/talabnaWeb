<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Photos;
use Illuminate\Auth\Access\Response;

class PhotosPolicy
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
    public function view(User $user, Photos $photos): bool
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
    public function update(User $user, Photos $photos): bool
    {
        //
    }

    /**
     * Determine whether the userController can delete the model.
     */
    public function delete(User $user, Photos $photos): bool
    {
        //
    }

    /**
     * Determine whether the userController can restore the model.
     */
    public function restore(User $user, Photos $photos): bool
    {
        //
    }

    /**
     * Determine whether the userController can permanently delete the model.
     */
    public function forceDelete(User $user, Photos $photos): bool
    {
        //
    }
}
