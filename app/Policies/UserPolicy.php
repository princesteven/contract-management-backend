<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\AdvancedAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization, AdvancedAuthorization;

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->can('view-users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users cannot edit their own profile AND must have edit-users permission
        return $user->can('edit-users') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->can('delete-users');
    }

    /**
     * Determine whether the user can activate the model.
     */
    public function activate(User $user, User $model): bool
    {
        // Users cannot activate themselves
        return $user->id !== $model->id && $user->can('activate-users');
    }

    /**
     * Determine whether the user can deactivate the model.
     */
    public function deactivate(User $user, User $model): bool
    {
        // Users cannot deactivate themselves
        return $user->id !== $model->id && $user->can('activate-users');
    }

    /**
     * Determine whether the user can assign roles to the model.
     */
    public function assignRole(User $user, User $model): bool
    {
        // Users cannot assign roles to themselves and must have assign-roles permission
        return $user->id !== $model->id && $user->can('assign-roles');
    }
}
