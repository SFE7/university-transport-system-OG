<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentSoumis;
use App\Models\IncidentBus;
use App\Models\LigneBus;
use App\Models\Membre;
use App\Models\Reservation;
use App\Models\Signalement;
use App\Models\Trajet;

class StatistiquesService
{
    public function getStats(): array
    {
        return [
            'membres' => [
                'total' => Membre::count(),
                'actifs' => Membre::where('is_active', true)->count(),
                'bannis' => Membre::where('is_banned', true)->count(),
                'par_role' => Membre::selectRaw('role, count(*) as total')->groupBy('role')->get(),
            ],
            'trajets' => [
                'total' => Trajet::count(),
                'actifs' => Trajet::where('status', 'active')->count(),
                'complets' => Trajet::where('status', 'completed')->count(),
                'annules' => Trajet::where('status', 'cancelled')->count(),
            ],
            'reservations' => [
                'total' => Reservation::count(),
                'en_attente' => Reservation::where('status', 'pending')->count(),
                'acceptees' => Reservation::where('status', 'accepted')->count(),
            ],
            'bus' => [
                'lignes' => LigneBus::count(),
                'chauffeurs' => Membre::where('role', 'chauffeur_bus')->count(),
                'incidents' => IncidentBus::whereNull('resolved_at')->count(),
            ],
            'signalements' => [
                'total' => Signalement::count(),
                'en_attente' => Signalement::where('status', 'en_attente')->count(),
            ],
            'documents' => [
                'en_attente' => DocumentSoumis::where('status', 'en_attente')->count(),
                'approuves' => DocumentSoumis::where('status', 'approuve')->count(),
                'rejetes' => DocumentSoumis::where('status', 'rejete')->count(),
            ],
        ];
    }
}
