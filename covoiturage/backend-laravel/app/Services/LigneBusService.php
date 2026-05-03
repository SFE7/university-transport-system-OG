<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\HoraireBus;
use App\Models\LigneBus;
use Illuminate\Support\Collection;

class LigneBusService
{
    public function getAll(): Collection
    {
        return LigneBus::where('is_active', true)
            ->with(['arrets' => function ($q) { $q->orderBy('order'); }, 'horaires'])
            ->get();
    }

    public function getOne(int $id): LigneBus
    {
        return LigneBus::with(['arrets' => function ($q) { $q->orderBy('order'); }, 'horaires'])
            ->findOrFail($id);
    }

    public function create(array $data): LigneBus
    {
        return LigneBus::create($data);
    }

    public function update(LigneBus $ligne, array $data): LigneBus
    {
        $ligne->update($data);
        return $ligne;
    }

    public function delete(LigneBus $ligne): void
    {
        $ligne->delete();
    }

    public function getSchedules(int $ligneId, ?string $day): Collection
    {
        $ligne = LigneBus::find($ligneId);
        if (! $ligne) {
            abort(404);
        }

        $query = HoraireBus::where('ligne_bus_id', $ligneId)->where('is_active', true);

        if ($day !== null) {
            $query->whereRaw("JSON_CONTAINS(days, '\"".$day."\"')");
        }

        return $query->with('chauffeur:id,name')->get();
    }
}
