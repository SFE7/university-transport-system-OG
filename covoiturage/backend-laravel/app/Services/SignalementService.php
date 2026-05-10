<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Signalement;
use App\Models\Membre;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SignalementService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Signalement::with(['reporter', 'reported'])->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(15);
    }

    public function create(array $data, Membre $reporter): Signalement
    {
        $data['reporter_id'] = $reporter->id;
        return Signalement::create($data);
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
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Membre;
use App\Models\Signalement;
use Illuminate\Pagination\LengthAwarePaginator;

class SignalementService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Signalement::with(['reporter', 'reported'])->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(15);
    }

    public function create(array $data, Membre $reporter): Signalement
    {
        return Signalement::create([
            'reporter_id' => $reporter->id,
            'reported_id' => $data['reported_id'],
            'reason' => $data['reason'],
            'status' => 'en_attente',
        ]);
    }

    public function updateStatus(Signalement $signalement, string $status): Signalement
    {
        abort_if(! in_array($status, ['en_attente', 'traite', 'archive'], true), 422);

        $signalement->status = $status;
        $signalement->save();

        return $signalement->fresh(['reporter', 'reported']);
    }

    public function delete(Signalement $signalement): void
    {
        $signalement->delete();
    }
}
