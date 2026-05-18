<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class PhotoController extends Controller
{
    public function show(string $category, string $model)
    {
        // Basic input validation to avoid path traversal and invalid chars
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $category) || !preg_match('/^[A-Za-z0-9_-]+$/', $model)) {
            abort(Response::HTTP_BAD_REQUEST);
        }

        $basePath = base_path('../../photos');
        $baseReal = realpath($basePath);
        $extensions = ['', 'png', 'jpg', 'jpeg', 'webp'];

        foreach ($extensions as $ext) {
            $candidate = $basePath . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $model . ($ext ? '.' . $ext : '');

            // Resolve real path and ensure it's inside the photos base directory
            $real = realpath($candidate);
            if ($real && $baseReal && strpos($real, $baseReal) === 0 && File::exists($real)) {
                return response()->file($real, [
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        abort(Response::HTTP_NOT_FOUND);
    }
}
