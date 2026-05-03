<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\IncidentBus;
use App\Models\Membre;
use Illuminate\Support\Collection;

class IncidentBusService
{
    public function getAll(): Collection
    {
        return IncidentBus::orderByDesc('created_at')
            ->with(['ligne:id,name', 'reporter:id,name'])
            ->get();
    }

    public function create(array $data, Membre $actor): IncidentBus
    {
        $payload = array_merge(['reported_by' => $actor->id], $data);
        return IncidentBus::create($payload);
    }

    public function resolve(IncidentBus $incident, Membre $actor): IncidentBus
    {
        if ($actor->role !== 'admin') {
            abort(403);
        }

        $incident->resolved_at = now();
        $incident->save();

        return $incident;
    }

    public function delete(IncidentBus $incident, Membre $actor): void
    {
        if ($actor->role !== 'admin') {
            abort(403);
        }

        $incident->delete();
    }
}
