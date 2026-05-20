<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/photos/{category}/{model}', function (string $category, string $model) {
    $basePath = base_path('../../photos');
    $extensions = ['', 'png', 'jpg', 'jpeg', 'webp'];

    foreach ($extensions as $extension) {
        $photoPath = $basePath . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $model . ($extension ? '.' . $extension : '');

        if (File::exists($photoPath)) {
            return response()->file($photoPath, [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    abort(Response::HTTP_NOT_FOUND);
});
