<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @param string $message
     * @param array $data
     * @return JsonResponse
     */
    public function returnResponse(string $message, array $data): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
        return response()->json($response, 200);
    }

    /**
     * return error response.
     * @param string $message
     * @param int $code
     * @param array $errorsArray
     * @return JsonResponse
     */
    public function returnError(string $message, int $code = 200, array $errorsArray = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'payload' => $errorsArray,
        ];

        return response()->json($response, $code);
    }
}
