<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ApprovedPhotoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = Auth::user();

        if (!$user) {
            // If no user is authenticated, show only approved photos
            $builder->where('is_approved', true);
        } else {
            $userType = $user->type; // e.g., 'App\Models\Creative', 'App\Models\Admin', etc.

            if (in_array($userType, ['App\Models\Admin', 'App\Models\SuperAdmin'])) {
                // Admins and SuperAdmins can see all photos
                // No additional query constraints needed
            } else {
                // For other users, show approved photos or photos uploaded by themselves
                $builder->where(function ($query) use ($user) {
                    $query->where('is_approved', true)
                        ->orWhere('user_id', $user->id);
                });
            }
        }
    }
}
