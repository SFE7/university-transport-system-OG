<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            return response()->json([
                'data' => null,
                'message' => $e->getMessage() ?: 'Server error',
                'status' => $this->getStatusCode($e),
            ], $this->getStatusCode($e));
        }

        return parent::render($request, $e);
    }

    private function getStatusCode(Throwable $e): int
    {
        if ($e instanceof \Illuminate\Auth\AuthenticationException) return 401;
        if ($e instanceof \Illuminate\Validation\ValidationException) return 422;
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) return $e->getStatusCode();
        return 500;
    }
}
