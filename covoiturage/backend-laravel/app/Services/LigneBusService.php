<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\HoraireBus;
use App\Models\LigneBus;
use App\Services\Contracts\LigneBusServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LigneBusService implements LigneBusServiceInterface
{
    public function getAll(): Collection
    {
        return LigneBus::where('is_active', true)
            ->with(['arrets' => function ($q) { $q->orderBy('order'); }, 'horaires'])
            ->get()
            ->map(function (LigneBus $ligne) {
                $ligne->next_departure = $this->getNextDeparture($ligne);
                return $ligne;
            });
    }


    public function getNextDeparture(LigneBus $ligne): ?string
    {
        $now = Carbon::now();
        $dayIndex = (int) $now->dayOfWeek; // 0 (Sun) - 6 (Sat)
        $daysMap = [
            0 => 'dimanche',
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
        ];

        $today = $daysMap[$dayIndex] ?? 'lundi';

        $query = HoraireBus::where('ligne_bus_id', $ligne->id)
            ->where('is_active', true)
            ->where('days', 'LIKE', '%"'.$today.'"%')
            ->where('departure_time', '>', $now->toTimeString())
            ->orderBy('departure_time');

        $next = $query->first();
        if (! $next) {
            return null;
        }

        try {
            $time = Carbon::createFromFormat('H:i:s', $next->departure_time)->format('H:i');
            return $time;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getOne(int $id): LigneBus
    {
        $ligne = LigneBus::with(['arrets' => function ($q) { $q->orderBy('order'); }, 'horaires'])
            ->findOrFail($id);

        $ligne->next_departure = $this->getNextDeparture($ligne);

        return $ligne;
    }

    public function create(array $data): LigneBus
    {
        $data['color'] = ($data['color'] ?? '') === '' ? '#00c853' : ($data['color'] ?? '#00c853');

        $arrets = $data['arrets'] ?? null;
        unset($data['arrets']);

        return DB::transaction(function () use ($data, $arrets): LigneBus {
            $ligne = LigneBus::create($data);

            if (is_array($arrets) && count($arrets)) {
                $ligne->arrets()->createMany($arrets);
                $ligne->load(['arrets' => function ($q) {
                    $q->orderBy('order');
                }]);
            }

            $ligne = $ligne->fresh(['arrets', 'horaires']);
            $ligne->next_departure = $this->getNextDeparture($ligne);

            return $ligne;
        });
    }

    public function update(LigneBus $ligne, array $data): LigneBus
    {
        if (array_key_exists('color', $data) && $data['color'] === '') {
            $data['color'] = '#00c853';
        }

        $arrets = $data['arrets'] ?? null;
        unset($data['arrets']);

        return DB::transaction(function () use ($ligne, $data, $arrets): LigneBus {
            $ligne->update($data);

            if (is_array($arrets)) {
                $ligne->arrets()->delete();
                if (count($arrets)) {
                    $ligne->arrets()->createMany($arrets);
                }
                $ligne->load(['arrets' => function ($q) {
                    $q->orderBy('order');
                }]);
            }

            $ligne = $ligne->fresh(['arrets', 'horaires']);
            $ligne->next_departure = $this->getNextDeparture($ligne);

            return $ligne;
        });
    }

    public function toggleActive(LigneBus $ligne): LigneBus
    {
        $ligne->update(['is_active' => ! $ligne->is_active]);
        return $ligne->fresh();
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
            $query->where('days', 'LIKE', '%"'.$day.'"%');
        }

        return $query->with('chauffeur:id,name')->get();
    }
}
