<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HoraireBus;
use App\Services\Contracts\HoraireBusServiceInterface;
use Illuminate\Support\Collection;

class HoraireBusService implements HoraireBusServiceInterface
{
    public function getAll(): Collection
    {
        return HoraireBus::query()
            ->with(['chauffeur:id,name', 'ligne:id,name'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getOne(int $id): HoraireBus
    {
        return HoraireBus::with(['chauffeur:id,name', 'ligne:id,name'])->findOrFail($id);
    }

    public function create(array $data): HoraireBus
    {
        return HoraireBus::create($data)
            ->load(['chauffeur:id,name', 'ligne:id,name']);
    }

    public function update(HoraireBus $horaire, array $data): HoraireBus
    {
        $horaire->update($data);
        $horaire->load(['chauffeur:id,name', 'ligne:id,name']);

        return $horaire;
    }

    public function delete(HoraireBus $horaire): void
    {
        $horaire->delete();
    }
}
