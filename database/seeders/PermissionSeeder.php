<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // User Management Permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'activate-users',
            'deactivate-users',
            
            // Role Management Permissions
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'assign-roles',
            
            // Permission Management
            'view-permissions',
            'manage-permissions',
            
            // Audit Log Management
            'view-audit-logs',
            'delete-audit-logs',
        ];

        // Create permissions only if they don't exist (idempotent)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api'
            ]);
        }

        $this->command->info('Permissions created successfully!');
    }
}
