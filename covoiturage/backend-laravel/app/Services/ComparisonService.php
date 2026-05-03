<?php
declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\TrajetResource;
use App\Models\HoraireBus;
use App\Models\Trajet;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ComparisonService
{
    public function compare(string $departure, string $arrival, string $datetime): array
    {
        $trajets = Trajet::where('departure_point', 'like', '%'.$departure.'%')
            ->where('arrival_point', 'like', '%'.$arrival.'%')
            ->where('departure_time', '>=', $datetime)
            ->where('status', 'active')
            ->with('conducteur')
            ->limit(10)
            ->get();

        $horaires = HoraireBus::where('is_active', true)
            ->with(['ligne', 'ligne.arrets'])
            ->get();

        $filtered = collect();
        foreach ($horaires as $horaire) {
            $ligne = $horaire->ligne;
            if (! $ligne || ! isset($ligne->arrets)) {
                continue;
            }

            $hasDeparture = $ligne->arrets->contains(function ($a) use ($departure) {
                return Str::contains($a->name, $departure);
            });
            $hasArrival = $ligne->arrets->contains(function ($a) use ($arrival) {
                return Str::contains($a->name, $arrival);
            });

            if ($hasDeparture && $hasArrival) {
                $filtered->push($horaire);
                if ($filtered->count() >= 10) {
                    break;
                }
            }
        }

        $earliestTrajet = $trajets->sortBy('departure_time')->first();
        $earliestHoraire = $filtered->sortBy('departure_time')->first();

        $fastest = 'covoiturage';
        if ($earliestHoraire && $earliestTrajet) {
            if ($earliestHoraire->departure_time < $earliestTrajet->departure_time) {
                $fastest = 'bus';
            }
        } elseif ($earliestHoraire && ! $earliestTrajet) {
            $fastest = 'bus';
        }

        $summary = [
            'fastest' => $fastest,
            'cheapest' => 'bus',
        ];

        return [
            'covoiturage' => TrajetResource::collection($trajets),
            'bus' => $filtered,
            'summary' => $summary,
        ];
    }
}
