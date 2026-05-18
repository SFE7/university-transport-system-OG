<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Avis;
use App\Models\Membre;
use App\Models\Reservation;
use App\Services\Contracts\AvisServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AvisService implements AvisServiceInterface
{
    public function getOne(int $id): Avis
    {
        return Avis::findOrFail($id);
    }

    public function getByConducteur(int $conducteurId): LengthAwarePaginator
    {
        return Avis::where('conducteur_id', $conducteurId)
            ->with(['reviewer'])
            ->paginate(15);
    }

    public function getAll(): LengthAwarePaginator
    {
        return Avis::with(['reviewer'])->paginate(15);
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

    public function update(Avis $avis, array $data, Membre $actor): Avis
    {
        abort_if($avis->reviewer_id !== $actor->id, 403);

        $avis->rating = $data['rating'];
        $avis->comment = $data['comment'] ?? null;
        $avis->save();

        return $avis;
    }

    public function delete(Avis $avis, Membre $actor): void
    {
        abort_if($avis->reviewer_id !== $actor->id, 403);

        $avis->delete();
    }
}
