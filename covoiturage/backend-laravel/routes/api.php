<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TrajetController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\AvisController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\BusPositionController;
use App\Http\Controllers\API\LigneBusController;
use App\Http\Controllers\API\IncidentBusController;
use App\Http\Controllers\API\ComparisonController;
use App\Http\Controllers\API\AdminMembreController;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::get('/trajets',        [TrajetController::class, 'index']);
    Route::get('/trajets/{id}',   [TrajetController::class, 'show'])->whereNumber('id');
    Route::get('/membres/{id}/avis', [AvisController::class, 'index'])->whereNumber('id');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::post('/trajets',            [TrajetController::class, 'store']);
        Route::get('/trajets/history',     [TrajetController::class, 'history']);
        Route::put('/trajets/{id}',        [TrajetController::class, 'update'])->whereNumber('id');
        Route::delete('/trajets/{id}',     [TrajetController::class, 'destroy'])->whereNumber('id');
        Route::get('/trajets/{id}',        [TrajetController::class, 'show'])->whereNumber('id');

        Route::get('/reservations',                  [ReservationController::class, 'index']);
        Route::post('/reservations',                 [ReservationController::class, 'store']);
        Route::get('/reservations/{id}',             [ReservationController::class, 'show'])->whereNumber('id');
        Route::delete('/reservations/{id}',          [ReservationController::class, 'destroy'])->whereNumber('id');
        Route::patch('/reservations/{id}/accept',    [ReservationController::class, 'accept'])->whereNumber('id');
        Route::patch('/reservations/{id}/refuse',    [ReservationController::class, 'refuse'])->whereNumber('id');

        Route::post('/avis',               [AvisController::class, 'store']);

        Route::get('/notifications',               [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read',   [NotificationController::class, 'markAsRead']);
    });

    // Sprint 2 public routes
    Route::get('/bus/positions',             [BusPositionController::class, 'index']);
    Route::get('/lignes',                    [LigneBusController::class, 'index']);
    Route::get('/lignes/{id}',               [LigneBusController::class, 'show'])->whereNumber('id');
    Route::get('/lignes/{id}/schedules',     [LigneBusController::class, 'schedules'])->whereNumber('id');
    Route::get('/incidents',                 [IncidentBusController::class, 'index']);

    // Sprint 2 protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('role:chauffeur_bus,conducteur')->group(function () {
            Route::patch('/bus/position',        [BusPositionController::class, 'update']);
            Route::patch('/bus/position/stop',   [BusPositionController::class, 'stopSharing']);
        });

        Route::get('/compare',               [ComparisonController::class, 'compare']);

        Route::middleware('role:admin')->group(function () {
            Route::post('/lignes',                       [LigneBusController::class, 'store']);
            Route::put('/lignes/{id}',                   [LigneBusController::class, 'update'])->whereNumber('id');
            Route::delete('/lignes/{id}',                [LigneBusController::class, 'destroy'])->whereNumber('id');
            Route::post('/incidents',                    [IncidentBusController::class, 'store']);
            Route::patch('/incidents/{id}/resolve',      [IncidentBusController::class, 'resolve'])->whereNumber('id');
            Route::delete('/incidents/{id}',             [IncidentBusController::class, 'destroy'])->whereNumber('id');
            Route::get('/admin/membres',                 [AdminMembreController::class, 'index']);
            Route::get('/admin/membres/{id}',            [AdminMembreController::class, 'show'])->whereNumber('id');
            Route::patch('/admin/membres/{id}/role',     [AdminMembreController::class, 'updateRole'])->whereNumber('id');
            Route::delete('/admin/membres/{id}',         [AdminMembreController::class, 'destroy'])->whereNumber('id');
        });
    });
});
