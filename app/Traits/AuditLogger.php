<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

trait AuditLogger
{
    /**
     * Log an action to the audit trail.
     */
    protected function logAction(string $action, Model $model = null, array $oldValues = [], array $newValues = []): void
    {
        $request = request();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now()
        ]);
    }

    /**
     * Log user creation action.
     */
    protected function logUserCreated(Model $user): void
    {
        $this->logAction('user.created', $user, [], $user->toArray());
    }

    /**
     * Log user update action.
     */
    protected function logUserUpdated(Model $user, array $originalData): void
    {
        $this->logAction('user.updated', $user, $originalData, $user->toArray());
    }

    /**
     * Log user activation action.
     */
    protected function logUserActivated(Model $user): void
    {
        $this->logAction('user.activated', $user);
    }

    /**
     * Log user deactivation action.
     */
    protected function logUserDeactivated(Model $user): void
    {
        $this->logAction('user.deactivated', $user);
    }

    /**
     * Log role assignment action.
     */
    protected function logRoleAssigned(Model $user, array $oldRoles, array $newRoles): void
    {
        $this->logAction('user.role_assigned', $user, ['roles' => $oldRoles], ['roles' => $newRoles]);
    }

    /**
     * Log role creation action.
     */
    protected function logRoleCreated(Model $role): void
    {
        $this->logAction('role.created', $role, [], $role->toArray());
    }

    /**
     * Log role update action.
     */
    protected function logRoleUpdated(Model $role, array $originalData): void
    {
        $this->logAction('role.updated', $role, $originalData, $role->toArray());
    }

    /**
     * Log role deletion action.
     */
    protected function logRoleDeleted(Model $role): void
    {
        $this->logAction('role.deleted', $role, $role->toArray(), []);
    }

    /**
     * Log permission view action.
     */
    protected function logPermissionsViewed(): void
    {
        $this->logAction('permissions.viewed');
    }
}
