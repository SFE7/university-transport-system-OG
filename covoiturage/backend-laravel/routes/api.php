<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TrajetController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\AvisController;
use App\Http\Controllers\API\NotificationController;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::get('/trajets',        [TrajetController::class, 'index']);
    Route::get('/trajets/{id}',   [TrajetController::class, 'show']);
    Route::get('/membres/{id}/avis', [AvisController::class, 'index']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::post('/trajets',            [TrajetController::class, 'store']);
        Route::get('/trajets/history',     [TrajetController::class, 'history']);
        Route::put('/trajets/{id}',        [TrajetController::class, 'update']);
        Route::delete('/trajets/{id}',     [TrajetController::class, 'destroy']);
        Route::get('/trajets/{id}',        [TrajetController::class, 'show']);

        Route::get('/reservations',                  [ReservationController::class, 'index']);
        Route::post('/reservations',                 [ReservationController::class, 'store']);
        Route::get('/reservations/{id}',             [ReservationController::class, 'show']);
        Route::delete('/reservations/{id}',          [ReservationController::class, 'destroy']);
        Route::patch('/reservations/{id}/accept',    [ReservationController::class, 'accept']);
        Route::patch('/reservations/{id}/refuse',    [ReservationController::class, 'refuse']);

        Route::post('/avis',               [AvisController::class, 'store']);

        Route::get('/notifications',               [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read',   [NotificationController::class, 'markAsRead']);
    });
});
