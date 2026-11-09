<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDocumentsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (in_array($user->role, ['admin', 'chauffeur_bus'], true)) {
            return $next($request);
        }

        if ($this->isWhitelisted($request)) {
            return $next($request);
        }

        if (! $user->has_verified_documents && in_array($user->role, ['membre', 'conducteur'], true)) {
            return response()->json([
                'message' => "Vos documents n'ont pas encore été vérifiés.",
                'code' => 'DOCUMENTS_NOT_VERIFIED',
            ], 403);
        }

        return $next($request);
    }

    private function isWhitelisted(Request $request): bool
    {
        $method = strtoupper($request->method());
        $path = ltrim($request->path(), '/');

        return ($method === 'POST' && $path === 'api/v1/auth/logout')
            || ($method === 'GET' && $path === 'api/v1/auth/me')
            || ($method === 'GET' && $path === 'api/v1/documents')
            || ($method === 'POST' && $path === 'api/v1/documents')
            || ($method === 'GET' && $path === 'api/v1/membres/profil')
            || ($method === 'PUT' && $path === 'api/v1/membres/profil')
            || ($method === 'PATCH' && $path === 'api/v1/auth/password');
    }
}