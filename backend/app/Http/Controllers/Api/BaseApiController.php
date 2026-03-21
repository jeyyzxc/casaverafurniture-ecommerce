<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

abstract class BaseApiController extends Controller
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    protected function errorResponse(
        string $message,
        int $statusCode = 400,
        mixed $errors = null,
        ?string $debugError = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($debugError !== null && config('app.debug')) {
            $response['error'] = $debugError;
        }

        return response()->json($response, $statusCode);
    }

    protected function handleException(
        \Exception $e,
        string $context,
        string $userMessage = 'An error occurred. Please try again.',
        int $statusCode = 500
    ): JsonResponse {
        Log::error("{$context} failed", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return $this->errorResponse(
            $userMessage,
            $statusCode,
            null,
            config('app.debug') ? $e->getMessage() : null
        );
    }

    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed.'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    protected function unauthorizedResponse(string $message = 'Unauthorized. Please log in.'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    protected function forbiddenResponse(string $message = 'Access denied.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }
}
