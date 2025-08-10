<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view any audit logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-audit-logs');
    }

    /**
     * Determine whether the user can view the audit log.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->can('view-audit-logs');
    }

    /**
     * Determine whether the user can create audit logs.
     * Note: Audit logs are typically created automatically by the system
     */
    public function create(User $user): bool
    {
        return false; // Audit logs should not be manually created
    }

    /**
     * Determine whether the user can update the audit log.
     * Note: Audit logs should be immutable
     */
    public function update(User $user, AuditLog $auditLog): bool
    {
        return false; // Audit logs should not be modified
    }

    /**
     * Determine whether the user can delete the audit log.
     */
    public function delete(User $user, AuditLog $auditLog): bool
    {
        return $user->can('delete-audit-logs');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
