<?php
declare(strict_types=1);

namespace App\Services;

use App\Events\BusApproachingAlert;
use App\Events\BusPositionUpdated;
use App\Models\ArretBus;
use App\Models\BusPosition;
use App\Models\HoraireBus;
use App\Models\Membre;
use App\Models\Reservation;
use App\Services\Contracts\BusPositionServiceInterface;
use Illuminate\Support\Collection;

class BusPositionService implements BusPositionServiceInterface
{
    public function updatePosition(Membre $chauffeur, array $data): BusPosition
    {
        if (!in_array($chauffeur->role, ['chauffeur_bus', 'conducteur'], true)) {
            abort(403);
        }

        $position = BusPosition::updateOrCreate(
            ['chauffeur_id' => $chauffeur->id],
            [
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'is_sharing' => $data['is_sharing'],
                'updated_at' => now(),
            ]
        );

        event(new BusPositionUpdated($position));

        $this->checkProximityAlerts($position);

        return $position;
    }

    public function stopSharing(Membre $chauffeur): BusPosition
    {
        if (!in_array($chauffeur->role, ['chauffeur_bus', 'conducteur'], true)) {
            abort(403);
        }

        $position = BusPosition::where('chauffeur_id', $chauffeur->id)->first();
        if (! $position) {
            abort(404);
        }

        $position->is_sharing = false;
        $position->updated_at = now();
        $position->save();

        return $position;
    }

    public function getActivePositions(): Collection
    {
        return BusPosition::where('is_sharing', true)
            ->with(['chauffeur:id,name'])
            ->get();
    }

    public function checkProximityAlerts(BusPosition $position): void
    {
        $arrets = ArretBus::with('ligne')->get();

        foreach ($arrets as $arret) {
            $distance = $this->haversineDistance(
                (float) $position->latitude,
                (float) $position->longitude,
                (float) $arret->latitude,
                (float) $arret->longitude
            );

            if ($distance <= 2.5) {
                $horaires = HoraireBus::where('ligne_bus_id', $arret->ligne->id)->get();
                foreach ($horaires as $horaire) {
                    $reservations = Reservation::whereHas('trajet', function ($q) use ($arret) {
                        $q->where('departure_point', 'like', '%'.$arret->name.'%');
                    })->get();

                    foreach ($reservations as $reservation) {
                        broadcast(new BusApproachingAlert(
                            $reservation->membre_id,
                            $arret->ligne->name,
                            5
                        ));
                    }
                }
            }
        }
    }

    private function haversineDistance(
        float $lat1, float $lon1,
        float $lat2, float $lon2
    ): float {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
