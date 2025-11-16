<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utils\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseApiController extends Controller
{
    /**
     * Standard success response
     */
    protected function successResponse(
        mixed $data = null,
        ?string $message = null,
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        return ApiResponse::success(
            $data,
            $message ?? 'Success',
            !empty($meta) ? $meta : null,
            $statusCode
        );
    }

    /**
     * Standard error response
     */
    protected function errorResponse(
        string $message,
        int $statusCode = 400,
        ?array $errors = null,
        mixed $data = null
    ): JsonResponse {
        return ApiResponse::error(
            $message,
            $errors,
            $statusCode,
            $data
        );
    }

    /**
     * Standard paginated response
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        mixed $data = null,
        array $additionalMeta = [],
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        if (!empty($additionalMeta)) {
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

            $meta = array_merge_recursive($meta, $additionalMeta);

            return ApiResponse::success(
                $data ?? $paginator->items(),
                $message,
                $meta
            );
        }

        return ApiResponse::paginated(
            $paginator,
            $data,
            $message
        );
    }

    /**
     * Standard validation error response
     */
    protected function validationErrorResponse(
        Validator $validator,
        string $message = 'Validation failed'
    ): JsonResponse {
        return ApiResponse::validationError(
            $validator->errors()->toArray(),
            $message
        );
    }
}