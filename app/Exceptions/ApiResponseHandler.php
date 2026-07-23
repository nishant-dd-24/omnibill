<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Domain\Context\CorrelationId;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiResponseHandler
{
    /**
     * Render the exception as a standardized JSON response.
     */
    public static function render(\Throwable $e, Request $request): JsonResponse
    {
        $correlationId = app()->bound(CorrelationId::class) ? app(CorrelationId::class)->id() : null;

        $statusCode = self::getStatusCode($e);

        $response = [
            'error' => [
                'code' => self::getErrorCode($e),
                'message' => self::getErrorMessage($e, $statusCode),
                'correlation_id' => $correlationId,
            ],
        ];

        if ($e instanceof ValidationException) {
            $response['error']['details'] = $e->errors();
        }

        return response()->json($response, $statusCode);
    }

    private static function getStatusCode(\Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }
        if ($e instanceof ValidationException) {
            return $e->status;
        }

        return 500;
    }

    private static function getErrorCode(\Throwable $e): string
    {
        if (method_exists($e, 'getErrorCode')) {
            return (string) $e->getErrorCode();
        }

        if ($e instanceof ValidationException) {
            return 'validation_failed';
        }

        if ($e instanceof HttpExceptionInterface) {
            return 'http_error_'.$e->getStatusCode();
        }

        return 'internal_error';
    }

    private static function getErrorMessage(\Throwable $e, int $statusCode): string
    {
        // Don't leak internal details on 500 unless in debug mode
        if ($statusCode >= 500 && ! config('app.debug')) {
            return 'An unexpected error occurred. Please contact support with your correlation_id.';
        }

        return $e->getMessage() ?: 'An error occurred.';
    }
}
