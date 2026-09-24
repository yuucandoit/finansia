<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Standard success JSON response.
     */
    protected function successResponse(mixed $data = null, string $message = 'Berhasil', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Standard error JSON response.
     */
    protected function errorResponse(string $message = 'Terjadi kesalahan', int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
