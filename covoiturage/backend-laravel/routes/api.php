<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TrajetController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\AvisController;
use App\Http\Controllers\API\ProfilController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\BusPositionController;
use App\Http\Controllers\API\LigneBusController;
use App\Http\Controllers\API\IncidentBusController;
use App\Http\Controllers\API\AdminMembreController;
use App\Http\Controllers\API\SignalementController;
use App\Http\Controllers\API\StatistiquesController;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::post('/auth/register/etudiant', [AuthController::class, 'registerEtudiant']);
    Route::post('/auth/register/professionnel', [AuthController::class, 'registerProfessionnel']);
    Route::post('/auth/register/conducteur', [AuthController::class, 'registerConducteur']);
    Route::get('/membres/{id}/profil', [ProfilController::class, 'show'])->whereNumber('id');
    Route::get('/trajets',        [TrajetController::class, 'index']);
    Route::get('/trajets/{id}',   [TrajetController::class, 'show'])->whereNumber('id');
    Route::get('/membres/{id}/avis', [AvisController::class, 'index'])->whereNumber('id');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::patch('/auth/password', [AuthController::class, 'changePassword'])->middleware('role:membre,conducteur,chauffeur_bus');
        Route::put('/membres/profil', [ProfilController::class, 'update']);
        Route::put('/conducteurs/vehicule', [ProfilController::class, 'updateVehicule'])->middleware('role:conducteur');

        Route::middleware('role:membre,conducteur')->group(function () {
            Route::post('/signalements', [SignalementController::class, 'store']);
        });

        Route::post('/trajets',            [TrajetController::class, 'store'])->middleware('role:conducteur');
        Route::get('/trajets/history',     [TrajetController::class, 'history'])->middleware('role:membre,conducteur');
        Route::put('/trajets/{id}',        [TrajetController::class, 'update'])->whereNumber('id');
        Route::delete('/trajets/{id}',     [TrajetController::class, 'destroy'])->whereNumber('id');
        Route::get('/trajets/{id}',        [TrajetController::class, 'show'])->whereNumber('id');

        Route::get('/reservations',                  [ReservationController::class, 'index']);
        Route::post('/reservations',                 [ReservationController::class, 'store'])->middleware('role:membre');
        Route::get('/reservations/{id}',             [ReservationController::class, 'show'])->whereNumber('id');
        Route::delete('/reservations/{id}',          [ReservationController::class, 'destroy'])->whereNumber('id')->middleware('role:membre,conducteur');
        Route::patch('/reservations/{id}/accept',    [ReservationController::class, 'accept'])->whereNumber('id')->middleware('role:conducteur');
        Route::patch('/reservations/{id}/refuse',    [ReservationController::class, 'refuse'])->whereNumber('id')->middleware('role:conducteur');

        Route::post('/avis',               [AvisController::class, 'store'])->middleware('role:membre');
        Route::put('/avis/{id}',            [AvisController::class, 'update'])->whereNumber('id')->middleware('role:membre');
        Route::delete('/avis/{id}',         [AvisController::class, 'destroy'])->whereNumber('id')->middleware('role:membre');

        Route::get('/notifications',               [NotificationController::class, 'index'])->middleware('role:membre,conducteur');
        Route::patch('/notifications/{id}/read',   [NotificationController::class, 'markAsRead'])->middleware('role:membre,conducteur');
    });

    // Sprint 2 public routes
    Route::get('/bus/positions',             [BusPositionController::class, 'index']);
    Route::get('/lignes',                    [LigneBusController::class, 'index']);
    Route::get('/lignes/{id}',               [LigneBusController::class, 'show'])->whereNumber('id');
    Route::get('/lignes/{id}/arrets',        function ($id) {
        $arrets = \App\Models\ArretBus::where('ligne_bus_id', $id)->orderBy('order')->get();
        return response()->json(['data' => $arrets]);
    })->whereNumber('id');
    Route::get('/lignes/{id}/schedules',     [LigneBusController::class, 'schedules'])->whereNumber('id');
    Route::get('/incidents',                 [IncidentBusController::class, 'index']);

    // Sprint 2 protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('role:chauffeur_bus')->group(function () {
            Route::patch('/bus/position',        [BusPositionController::class, 'update']);
            Route::patch('/bus/position/stop',   [BusPositionController::class, 'stopSharing']);
        });

        // Compare feature removed

        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/statistiques',               [\App\Http\Controllers\API\StatistiquesController::class, 'index']);
            Route::get('/admin/signalements',                   [SignalementController::class, 'index']);
            Route::patch('/admin/signalements/{id}/statut',     [SignalementController::class, 'updateStatus'])->whereNumber('id');
            Route::delete('/admin/signalements/{id}',           [SignalementController::class, 'destroy'])->whereNumber('id');
            Route::get('/documents', [DocumentController::class, 'index']);
            Route::patch('/documents/{id}/approve', [DocumentController::class, 'approve'])->whereNumber('id');
            Route::patch('/documents/{id}/reject', [DocumentController::class, 'reject'])->whereNumber('id');
            Route::post('/lignes',                       [LigneBusController::class, 'store']);
            Route::put('/lignes/{id}',                   [LigneBusController::class, 'update'])->whereNumber('id');
            Route::delete('/lignes/{id}',                [LigneBusController::class, 'destroy'])->whereNumber('id');
            Route::patch('/lignes/{id}/toggle',          [LigneBusController::class, 'toggleActive'])->whereNumber('id');
            // Arrets (stops)
            Route::post('/arrets',                       [\App\Http\Controllers\API\ArretBusController::class, 'store']);
            Route::put('/arrets/{id}',                   [\App\Http\Controllers\API\ArretBusController::class, 'update'])->whereNumber('id');
            Route::delete('/arrets/{id}',                [\App\Http\Controllers\API\ArretBusController::class, 'destroy'])->whereNumber('id');
            // Horaires
            Route::post('/horaires',                     [\App\Http\Controllers\API\HoraireController::class, 'store']);
            Route::put('/horaires/{id}',                 [\App\Http\Controllers\API\HoraireController::class, 'update'])->whereNumber('id');
            Route::delete('/horaires/{id}',              [\App\Http\Controllers\API\HoraireController::class, 'destroy'])->whereNumber('id');
            Route::post('/incidents',                    [IncidentBusController::class, 'store']);
            Route::patch('/incidents/{id}/resolve',      [IncidentBusController::class, 'resolve'])->whereNumber('id');
            Route::delete('/incidents/{id}',             [IncidentBusController::class, 'destroy'])->whereNumber('id');
            Route::get('/admin/membres',                 [AdminMembreController::class, 'index']);
            Route::get('/admin/membres/{id}',            [AdminMembreController::class, 'show'])->whereNumber('id');
            Route::patch('/admin/membres/{id}/role',     [AdminMembreController::class, 'updateRole'])->whereNumber('id');
            Route::delete('/admin/membres/{id}',         [AdminMembreController::class, 'destroy'])->whereNumber('id');
            Route::post('/admin/membres/{id}/send-credentials', [AdminMembreController::class, 'sendCredentials'])->whereNumber('id');
            Route::patch('/admin/membres/{id}/suspend',          [AdminMembreController::class, 'toggleSuspend'])->whereNumber('id');
            Route::patch('/admin/membres/{id}/bannir',           [AdminMembreController::class, 'ban'])->whereNumber('id');
        });
    });
});
