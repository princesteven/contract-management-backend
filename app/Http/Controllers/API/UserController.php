<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
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
                'success' => true,
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
        try {
            $user = new User();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->password = Hash::make('password'); // Default password
            $user->isActive = true; // Set user as active by default
            $user->save();

            return $this->returnResponse('User created successfully', [
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return $this->returnError('Failed to create user', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreateUserRequest $request, User $user)
    {
        //
    }

    /**
     * Deactivate user
     * @param User $user
     * @return void
     */
    public function deactivate(User $user)
    {

    }

    /**
     * Activate user
     * @param User $user
     * @return void
     */
    public function activate(User $user)
    {

    }
}
