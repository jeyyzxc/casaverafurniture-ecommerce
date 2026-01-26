<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Base API Controller
 * Provides common functionality for all API controllers
 */
abstract class BaseApiController extends Controller
{
    /**
     * Return a successful JSON response
     */
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

    /**
     * Return an error JSON response
     */
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

    /**
     * Handle exceptions with proper logging and response
     */
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

    /**
     * Return validation error response
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed.'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized. Please log in.'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return forbidden response
     */
    protected function forbiddenResponse(string $message = 'Access denied.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }
}
