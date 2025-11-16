<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    /**
     * Return a success JSON response
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        mixed $meta = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error JSON response
     */
    public static function error(
        string $message,
        mixed $errors = null,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        mixed $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response
     */
    public static function validationError(
        mixed $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error(
            $message,
            $errors,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Return a not found error response
     */
    public static function notFound(
        string $message = 'Resource not found'
    ): JsonResponse {
        return self::error(
            $message,
            null,
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Return an unauthorized error response
     */
    public static function unauthorized(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return self::error(
            $message,
            null,
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Return a forbidden error response
     */
    public static function forbidden(
        string $message = 'Forbidden'
    ): JsonResponse {
        return self::error(
            $message,
            null,
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Return an internal server error response
     */
    public static function serverError(
        string $message = 'Internal server error'
    ): JsonResponse {
        return self::error(
            $message,
            null,
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Return a created response
     */
    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return self::success(
            $data,
            $message,
            null,
            Response::HTTP_CREATED
        );
    }

    /**
     * Return a no content response
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Return a paginated response
     */
    public static function paginated(
        \Illuminate\Pagination\LengthAwarePaginator $paginator,
        mixed $data = null,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        $payload = $data ?? $paginator->items();
        $meta = [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
                'path' => $paginator->path(),
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
        ];

        return self::success($payload, $message, $meta);
    }

    /**
     * Return a collection response
     */
    public static function collection(
        mixed $collection,
        string $message = 'Data retrieved successfully',
        mixed $meta = null
    ): JsonResponse {
        return self::success($collection, $message, $meta);
    }

    /**
     * Return a single resource response
     */
    public static function resource(
        mixed $resource,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        return self::success($resource, $message);
    }
}
