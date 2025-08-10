<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class UnauthorizedAccessHandler
{
    /**
     * Log unauthorized access attempt and return consistent API response
     *
     * @param Request $request
     * @param Throwable $exception
     * @return JsonResponse|null
     */
    public static function handle(Request $request, Throwable $exception): ?JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized API access attempt', [
                'user_id' => $request->user()?->id,
                'username' => $request->user()?->username,
                'email' => $request->user()?->email,
                'endpoint' => $request->getMethod() . ' ' . $request->getPathInfo(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => $request->all(),
                'exception_message' => $exception->getMessage(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action.',
                'payload' => [
                    'error' => 'Insufficient permissions'
                ]
            ], 403);
        }

        return null;
    }
}
