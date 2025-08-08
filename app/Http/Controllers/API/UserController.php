<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
