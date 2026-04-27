<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function success($data, string $message = 'success', int $status = 200): JsonResponse
    {
        return response()->json([
            'data'    => $data,
            'message' => $message,
            'status'  => $status,
        ], $status);
    }

    protected function error(string $message, int $status, $errors = null): JsonResponse
    {
        return response()->json([
            'data'    => $errors,
            'message' => $message,
            'status'  => $status,
        ], $status);
    }
}
