<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Signalement;
use App\Models\Membre;
use App\Models\Trajet;
use App\Services\Contracts\SignalementServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SignalementService implements SignalementServiceInterface
{
    public function getOne(int $id): Signalement
    {
        return Signalement::with(['membre', 'conducteur', 'trajet'])->findOrFail($id);
    }

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Signalement::with(['membre', 'conducteur', 'trajet'])->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(15);
    }

    public function create(array $data, Membre $reporter): Signalement
    {
        $conducteurId = (int) $data['conducteur_id'];
        $trajetId = isset($data['trajet_id']) && $data['trajet_id'] !== '' ? (int) $data['trajet_id'] : null;

        if ($trajetId !== null) {
            $trajet = Trajet::query()->findOrFail($trajetId);
            abort_if((int) $trajet->membre_id !== $conducteurId, 422, 'Ce trajet ne correspond pas à ce conducteur.');
        }

        $duplicateQuery = Signalement::query()
            ->where('reporter_id', $reporter->id)
            ->where('conducteur_id', $conducteurId);

        if ($trajetId === null) {
            $duplicateQuery->whereNull('trajet_id');
        } else {
            $duplicateQuery->where('trajet_id', $trajetId);
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'conducteur_id' => 'Vous avez déjà signalé ce conducteur pour ce trajet.',
            ]);
        }

        $signalement = Signalement::create([
            'reporter_id' => $reporter->id,
            'reported_id' => $conducteurId,
            'conducteur_id' => $conducteurId,
            'trajet_id' => $trajetId,
            'reason' => (string) $data['raison'],
            'description' => $data['description'] ?? null,
        ]);

        return $signalement->load(['membre', 'conducteur', 'trajet']);
    }

    public function updateStatus(Signalement $signalement, string $status): Signalement
    {
        if (! in_array($status, ['en_attente', 'traite', 'archive'], true)) {
            throw new \InvalidArgumentException('Invalid status');
        }

        $signalement->status = $status;
        $signalement->save();
        return $signalement;
    }

    public function delete(Signalement $signalement): void
    {
        $signalement->delete();
    }
}
