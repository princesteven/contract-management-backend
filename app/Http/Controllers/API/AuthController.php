<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Http\Requests\RefreshTokenRequest;

class AuthController extends BaseController
{
    /**
     * Login user and create token
     *
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            // Check if user is active
            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                return $this->returnError("Can't generate token for Deactivated user. Please contact administrator", 403);
            }

            // Revoke all previous tokens before issuing new ones
            $user->tokens()->where('revoked', false)->get()->each(function ($token) {
                $token->revoke();
            });

            // Get your OAuth client credentials
            $client = Client::whereJsonContains('grant_types', 'password')
                ->orWhereJsonContains('grant_types', 'refresh_token')
                ->first();

            if (!$client) {
                return $this->returnError('Password client not found', 500);
            }

            // Make internal request to get tokens
            $tokenRequest = Request::create('/oauth/token', 'POST', [
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => "G0r9EWnALDLXW6HGcnfJCX75EWDzHQUf0v7BuXpS",
                'username' => $request->username,
                'password' => $request->password,
                'scope' => '*',
            ]);

            $tokenResponse = app()->handle($tokenRequest);
            $tokenData = json_decode($tokenResponse->getContent(), true);

            if ($tokenResponse->getStatusCode() === 200) {
                // Load user with roles and permissions for response
                $userWithPermissions = Auth::user()->load('roles.permissions');
                $permissions = $userWithPermissions->getAllPermissions()->pluck('name')->toArray();

                return $this->returnResponse('Login successful', [
                    'tokens' => [
                        'access_token' => $tokenData['access_token'],
                        'refresh_token' => $tokenData['refresh_token'],
                    ],
                    'user' => [
                        'id' => $userWithPermissions->id,
                        'name' => $userWithPermissions->name,
                        'username' => $userWithPermissions->username,
                        'email' => $userWithPermissions->email,
                        'is_active' => $userWithPermissions->is_active,
                        'roles' => $userWithPermissions->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                            ];
                        }),
                        'permissions' => $permissions
                    ]
                ]);
            }

            return $this->returnError("Failed to generate tokens", 500, $tokenData);
        }

        return $this->returnError('Invalid credentials', 401);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        // Get your OAuth client credentials
        $client = Client::whereJsonContains('grant_types', 'password')
            ->orWhereJsonContains('grant_types', 'refresh_token')
            ->first();

        if (!$client) {
            return $this->returnError('Refresh client not found', 500);
        }

        // Make internal request to get tokens
        $tokenRequest = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refreshToken,
            'client_id' => $client->id,
            'client_secret' => "G0r9EWnALDLXW6HGcnfJCX75EWDzHQUf0v7BuXpS",
            'scope' => '*',
        ]);

        $tokenResponse = app()->handle($tokenRequest);
        $tokenData = json_decode($tokenResponse->getContent(), true);

        if ($tokenResponse->getStatusCode() === 200) {
            return $this->returnResponse('Login successful', [
                'tokens' => [
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                ],
                //'user' => User::find($accessToken->user_id)
            ]);
        }

        return $this->returnError("Failed to generate tokens", 500, $tokenData);
    }

    /**
     * Get authenticated user with roles and permissions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAuthenticatedUser(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles.permissions');
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        return $this->returnResponse('User data retrieved successfully', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                }),
                'permissions' => $permissions
            ]
        ]);
    }
}
