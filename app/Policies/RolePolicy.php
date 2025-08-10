<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-roles');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(User $user): bool
    {
        return $user->can('view-roles');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->can('create-roles');
    }

    /**
     * Determine whether the user can update the role.
     */
    public function update(User $user): bool
    {
        return $user->can('edit-roles');
    }

    /**
     * Determine whether the user can delete the role.
     */
    public function delete(User $user): bool
    {
        return $user->can('delete-roles');
    }
}
