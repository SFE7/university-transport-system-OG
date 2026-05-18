<?php

namespace App\Providers;

use App\Services\Contracts\TrajetServiceInterface;
use App\Services\Contracts\ReservationServiceInterface;
use App\Services\Contracts\AvisServiceInterface;
use App\Services\Contracts\ProfilServiceInterface;
use App\Services\Contracts\AdminMembreServiceInterface;
use App\Services\Contracts\ChauffeurServiceInterface;
use App\Services\Contracts\NotificationServiceInterface;
use App\Services\Contracts\LigneBusServiceInterface;
use App\Services\Contracts\HoraireBusServiceInterface;
use App\Services\Contracts\IncidentBusServiceInterface;
use App\Services\Contracts\BusPositionServiceInterface;
use App\Services\Contracts\ArretBusServiceInterface;
use App\Services\Contracts\SignalementServiceInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\DocumentServiceInterface;
use App\Services\TrajetService;
use App\Services\ReservationService;
use App\Services\AvisService;
use App\Services\ProfilService;
use App\Services\AdminMembreService;
use App\Services\ChauffeurService;
use App\Services\NotificationService;
use App\Services\LigneBusService;
use App\Services\HoraireBusService;
use App\Services\IncidentBusService;
use App\Services\BusPositionService;
use App\Services\ArretBusService;
use App\Services\SignalementService;
use App\Services\AuthService;
use App\Services\DocumentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TrajetServiceInterface::class,
            TrajetService::class
        );

        $this->app->bind(
            ReservationServiceInterface::class,
            ReservationService::class
        );

        $this->app->bind(
            AvisServiceInterface::class,
            AvisService::class
        );

        $this->app->bind(
            ProfilServiceInterface::class,
            ProfilService::class
        );

        $this->app->bind(
            AdminMembreServiceInterface::class,
            AdminMembreService::class
        );

        $this->app->bind(
            ChauffeurServiceInterface::class,
            ChauffeurService::class
        );

        $this->app->bind(
            NotificationServiceInterface::class,
            NotificationService::class
        );

        $this->app->bind(
            LigneBusServiceInterface::class,
            LigneBusService::class
        );

        $this->app->bind(
            HoraireBusServiceInterface::class,
            HoraireBusService::class
        );

        $this->app->bind(
            IncidentBusServiceInterface::class,
            IncidentBusService::class
        );

        $this->app->bind(
            BusPositionServiceInterface::class,
            BusPositionService::class
        );

        $this->app->bind(
            ArretBusServiceInterface::class,
            ArretBusService::class
        );

        $this->app->bind(
            SignalementServiceInterface::class,
            SignalementService::class
        );

        $this->app->bind(
            AuthServiceInterface::class,
            AuthService::class
        );

        $this->app->bind(
            DocumentServiceInterface::class,
            DocumentService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
