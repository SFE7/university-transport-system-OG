<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Avis;
use App\Models\Membre;
use App\Models\Reservation;
use Illuminate\Pagination\LengthAwarePaginator;

class AvisService
{
    public function getByConducteur(int $conducteurId): LengthAwarePaginator
    {
        return Avis::where('conducteur_id', $conducteurId)
            ->with(['reviewer'])
            ->paginate(15);
    }

    public function create(array $data, Membre $actor): Avis
    {
        $reservation = Reservation::where('membre_id', $actor->id)
            ->where('trajet_id', $data['trajet_id'])
            ->first();

        abort_if(!$reservation, 422);

        abort_if(
            $reservation->status !== 'accepted',
            422,
        );

        $existingAvis = Avis::where('reviewer_id', $actor->id)
            ->where('trajet_id', $data['trajet_id'])
            ->first();

        abort_if($existingAvis, 422);

        return Avis::create([
            'reviewer_id' => $actor->id,
            'conducteur_id' => $data['conducteur_id'],
            'trajet_id' => $data['trajet_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);
    }
}
