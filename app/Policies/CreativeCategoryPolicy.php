<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CreativeCategory;
use App\Models\User;

class CreativeCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CreativeCategory $creativeCategory): bool
    {
       //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Request denied.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CreativeCategory $creativeCategory): Response
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Request denied.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CreativeCategory $creativeCategory): Response
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Request denied.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CreativeCategory $creativeCategory): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CreativeCategory $creativeCategory): bool
    {
        //
    }
}
