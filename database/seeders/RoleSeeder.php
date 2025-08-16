<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles with their permissions
        $rolesWithPermissions = [
            'super-admin' => [
                'view-users', 'create-users', 'edit-users', 'delete-users', 'activate-users', 'deactivate-users',
                'view-roles', 'create-roles', 'edit-roles', 'delete-roles', 'assign-roles',
                'view-permissions', 'manage-permissions',
                'view-audit-logs', 'delete-audit-logs',
                'view-contracts', 'create-contracts', 'edit-contracts',
                'view-contract-counter-parties', 'create-contract-counter-parties', 'edit-contract-counter-parties',
                'view-business-units', 'create-business-units', 'edit-business-units'
            ],
            'admin' => [
                'view-users', 'create-users', 'edit-users', 'activate-users', 'deactivate-users',
                'view-roles', 'assign-roles',
                'view-permissions',
                'view-audit-logs',
                'view-contracts', 'create-contracts', 'edit-contracts',
                'view-contract-counter-parties', 'create-contract-counter-parties', 'edit-contract-counter-parties',
                'view-business-units', 'create-business-units', 'edit-business-units'
            ],
            'manager' => [
                'view-users', 'create-users', 'edit-users',
                'view-roles',
                'view-permissions',
                'view-contracts', 'create-contracts', 'edit-contracts',
                'view-contract-counter-parties',
                'view-business-units'
            ],
            'user' => [
                'view-users',
                'view-permissions',
                'view-contracts',
                'view-contract-counter-parties',
                'view-business-units'
            ]
        ];

        // Create roles and assign permissions
        foreach ($rolesWithPermissions as $roleName => $permissionNames) {
            // Create role if it doesn't exist
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api',
                'is_active' => true
            ]);

            // Get permissions by name and guard
            $permissions = Permission::where('guard_name', 'api')->whereIn('name', $permissionNames)->get();
            
            // Sync permissions to role
            $role->syncPermissions($permissions);

            $this->command->info("Role '{$roleName}' created with " . count($permissions) . " permissions.");
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
