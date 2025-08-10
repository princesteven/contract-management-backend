<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

trait AdvancedAuthorization
{
    /**
     * Check if user has any of the specified permissions.
     */
    protected function hasAnyPermission(array $permissions): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the specified permissions.
     */
    protected function hasAllPermissions(array $permissions): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$user->can($permission)) {
                return false;
            }
        }

        return true;
    }
}
