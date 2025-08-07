<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client;
use Laravel\Passport\RefreshToken;

class AuthController extends BaseController
{
    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refreshToken(RefreshTokenRequest $request)
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
//                'user' => User::find($accessToken->user_id)
            ]);
        }

        return $this->returnError("Failed to generate tokens", 500, $tokenData);
    }
}
