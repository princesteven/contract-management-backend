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
                return $this->returnResponse('Login successful', [
                    'success' => true,
                    'tokens' => [
                        'access_token' => $tokenData['access_token'],
                        'refresh_token' => $tokenData['refresh_token'],
                    ],
                    'user' => Auth::user()
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
                'success' => true,
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
     * @param Request $request
     * @return JsonResponse
     */
    public function getAuthenticatedUser(Request $request): JsonResponse
    {
        return $this->returnResponse('User data retrieved successfully', [
            'success' => true,
            'user' => $request->user()
        ]);
    }
}
