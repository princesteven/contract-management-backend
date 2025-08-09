<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends BaseController
{
    /**
     * Display a listing of roles.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            
            // Handle special case for returning all roles
            if ($limit === '*') {
                $roles = Role::all();
            } else {
                $roles = Role::limit($limit)->get();
            }

            return $this->returnResponse('Roles retrieved successfully', [
                'roles' => $roles
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to retrieve roles', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified role with permissions.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $role = Role::with('permissions')->find($id);

            if (!$role) {
                return $this->returnError('Role not found', 404);
            }

            return $this->returnResponse('Role retrieved successfully', [
                'role' => $role
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to retrieve role', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created role.
     * 
     * @param CreateRoleRequest $request
     * @return JsonResponse
     */
    public function store(CreateRoleRequest $request): JsonResponse
    {
        try {
            // Create role
            $role = Role::create([
                'name' => $request->name,
                'is_active' => $request->is_active ?? true
            ]);

            // Assign permissions if provided
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            // Load permissions for response
            $role->load('permissions');

            return $this->returnResponse('Role created successfully', [
                'role' => $role
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to create role', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified role.
     * 
     * @param UpdateRoleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->returnError('Role not found', 404);
            }

            // Update role basic info
            if ($request->has('name')) {
                $role->name = $request->name;
            }

            if ($request->has('is_active')) {
                $role->is_active = $request->is_active;
            }

            $role->save();

            // Update permissions only if permissions key exists in request
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            // Load permissions for response
            $role->load('permissions');

            return $this->returnResponse('Role updated successfully', [
                'role' => $role
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to update role', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified role.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->returnError('Role not found', 404);
            }

            $role->delete();

            return $this->returnResponse('Role deleted successfully', []);
        } catch (\Exception $e) {
            return $this->returnError('Failed to delete role', 500, ['error' => $e->getMessage()]);
        }
    }
}
