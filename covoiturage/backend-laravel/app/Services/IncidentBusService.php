<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\IncidentBus;
use App\Models\Membre;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data, $actor): IncidentBus {
            $incident = IncidentBus::create(array_merge(['reported_by' => $actor->id], $data))
                ->load(['ligne:id,name', 'reporter:id,name']);

            $lineName = $incident->ligne?->name ?? 'ligne inconnue';
            $reporterName = $incident->reporter?->name ?? 'Utilisateur inconnu';
            $message = sprintf(
                'Incident bus #%d sur la ligne %s. Type: %s. Description: %s. Signalé par: %s.',
                $incident->id,
                $lineName,
                $incident->type,
                $incident->description,
                $reporterName
            );

            foreach (Membre::where('role', 'membre')->pluck('id') as $membreId) {
                Notification::create([
                    'membre_id' => $membreId,
                    'message' => $message,
                    'type' => 'incident_reported',
                    'target_role' => 'membre',
                    'is_read' => false,
                ]);
            }

            return $incident;
        });
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
