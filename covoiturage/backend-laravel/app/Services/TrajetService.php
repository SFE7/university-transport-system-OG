<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Membre;
use App\Models\Trajet;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class TrajetService
{
    public function autoCompleteExpired(): void
    {
        Trajet::query()
            ->where('status', 'active')
            ->where('departure_time', '<', now())
            ->update(['status' => 'completed']);
    }

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $this->autoCompleteExpired();

        $query = Trajet::with(['conducteur'])
            ->where('status', 'active');

        if (isset($filters['departure_point'])) {
            $query->where('departure_point', 'LIKE', '%' . $filters['departure_point'] . '%');
        }

        if (isset($filters['arrival_point'])) {
            $query->where('arrival_point', 'LIKE', '%' . $filters['arrival_point'] . '%');
        }

        if (isset($filters['departure_time'])) {
            $query->whereDate('departure_time', $filters['departure_time']);
        }

        if (isset($filters['available_seats'])) {
            $query->where('available_seats', '>=', $filters['available_seats']);
        }

        return $query->paginate(10);
    }

    public function getOne(int $id): Trajet
    {
        return Trajet::with(['conducteur', 'reservations'])->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $carPhoto, Membre $actor): Trajet
    {
        abort_if($actor->role !== 'conducteur', 403);

        if ($carPhoto) {
            $storedPath = $carPhoto->store('car_photos', 'public');
            $data['car_photo_url'] = Storage::url($storedPath);
        }

        $data['car_category'] = $data['car_category'] ?? null;
        $data['car_model'] = $data['car_model'] ?? null;
        $data['car_photo_url'] = $data['car_photo_url'] ?? null;

        $data['membre_id'] = $actor->id;

        return Trajet::create($data);
    }

    public function update(Trajet $trajet, array $data, Membre $actor): Trajet
    {
        abort_if($trajet->membre_id !== $actor->id, 403);

        $trajet->update($data);

        return $trajet->fresh();
    }

    public function cancel(Trajet $trajet, Membre $actor): Trajet
    {
        abort_if($trajet->membre_id !== $actor->id, 403);

        $trajet->status = 'cancelled';
        $trajet->save();

        return $trajet;
    }

    public function getHistory(Membre $actor): LengthAwarePaginator
    {
        $this->autoCompleteExpired();

        return Trajet::query()
            ->with(['conducteur', 'reservations'])
            ->whereIn('status', ['completed', 'cancelled'])
            ->where(function ($query) use ($actor): void {
                $query->where('membre_id', $actor->id)
                    ->orWhereHas('reservations', function ($reservationQuery) use ($actor): void {
                        $reservationQuery->where('membre_id', $actor->id);
                    });
            })
            ->orderByDesc('departure_time')
            ->paginate(15);
    }

    public function getMyTrajets(Membre $actor): LengthAwarePaginator
    {
        abort_if($actor->role !== 'conducteur', 403);

        return Trajet::where('membre_id', $actor->id)
            ->with(['conducteur'])
            ->orderByDesc('departure_time')
            ->paginate(15);
    }
}
