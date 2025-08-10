<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\AuditLogger;
use App\Traits\AdvancedAuthorization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends BaseController
{
    use AuditLogger;
    use AdvancedAuthorization;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        try {
            $limit = $request->input('limit', 10);
            $query = User::query();

            // Apply filters if provided
            if ($request->has('username')) {
                $query->where('username', $request->username);
            }

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('status')) {
                $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
                $query->where('is_active', $isActive);
            }

            $users = $query->limit($limit)->get();

            return $this->returnResponse('Users retrieved successfully', [
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to retrieve users', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new user
     *
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        try {
            $user = new User();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->password = Hash::make('password'); // Default password
            $user->is_active = true; // Set user as active by default
            $user->save();

            // Assign roles if provided
            if ($request->has('roles')) {
                $this->authorize('assignRole', $user);

                // Capture old roles (empty for new user)
                $oldRoles = [];

                $roles = Role::whereIn('id', $request->roles)->get();
                $user->syncRoles($roles);

                // Capture new roles for audit
                $newRoles = $roles->map(function ($role) {
                    return ['id' => $role->id, 'name' => $role->name];
                })->toArray();

                $this->logRoleAssigned($user, $oldRoles, $newRoles);
            }

            // Load roles for response
            $user->load('roles');

            $this->logUserCreated($user);

            return $this->returnResponse('User created successfully', [
                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to create user', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $this->authorize('view', User::class);

        try {
            $user = User::with('roles')->find($id);

            if (!$user) {
                return $this->returnError('User not found', 404);
            }

            return $this->returnResponse('User retrieved successfully', [
                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to retrieve user', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->returnError('User not found', 404);
            }

            $this->authorize('update', $user);

            // Store original data for audit logging
            $originalData = $user->getOriginal();

            // Update only the fields that are present in the request
            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            if ($request->has('is_active')) {
                $user->is_active = $request->is_active;
            }

            $user->save();

            // Update roles if provided (sync operation)
            if ($request->has('roles')) {
                $this->authorize('assignRole', $user);

                // Capture old roles before making changes
                $oldRoles = $user->roles->map(function ($role) {
                    return ['id' => $role->id, 'name' => $role->name];
                })->toArray();

                $roles = Role::whereIn('id', $request->roles)->get();
                $user->syncRoles($roles);

                // Capture new roles for audit
                $newRoles = $roles->map(function ($role) {
                    return ['id' => $role->id, 'name' => $role->name];
                })->toArray();

                $this->logRoleAssigned($user, $oldRoles, $newRoles);
            }

            // Load roles for response
            $user->load('roles');

            $this->logUserUpdated($user, $originalData);

            return $this->returnResponse('User updated successfully', [
                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to update user', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Deactivate user
     * @param int $id
     * @return JsonResponse
     */
    public function deactivate(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->returnError('User not found', 404);
            }

            $this->authorize('deactivate', $user);

            $user->is_active = false;
            $user->save();

            // Revoke all active tokens for this user
            $user->tokens()->where('revoked', false)->get()->each(function ($token) {
                $token->revoke();
            });

            $this->logUserDeactivated($user);

            return $this->returnResponse('User deactivated successfully', [
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to deactivate user', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Activate user
     * @param int $id
     * @return JsonResponse
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->returnError('User not found', 404);
            }

            $this->authorize('activate', $user);

            $user->is_active = true;
            $user->save();

            $this->logUserActivated($user);

            return $this->returnResponse('User activated successfully', [
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to activate user', 500, ['error' => $e->getMessage()]);
        }
    }
}
