<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Photo $photo): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if ($user->isCreative()) {
            return Response::allow();
        }

        return Response::deny('You are not allowed to upload images');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Photo $photo): Response
    {
        if ($user->isCreative() && $user->id === $photo->user_id)
        {
            return Response::allow();
        }

        if ($user->isAdmin())
        {
            return Response::allow();
        }

        return Response::deny('You are not allowed to update this resource');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Photo $photo): Response
    {
        if ($user->isCreative() && $user->id === $photo->user_id)
        {
            return Response::allow();
        }

        if ($user->isAdmin())
        {
            return Response::allow();
        }

        return Response::deny('You are not allowed to delete this resource');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Photo $photo): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Photo $photo): bool
    {
        //
    }

    public function approve(User $user)
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Request denied.');
    }
}