<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Membre;
use App\Models\Signalement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SignalementServiceInterface
{
    public function getOne(int $id): Signalement;

    public function getAll(array $filters = []): LengthAwarePaginator;

    public function create(array $data, Membre $reporter): Signalement;

    public function updateStatus(Signalement $signalement, string $status): Signalement;

    public function delete(Signalement $signalement): void;
}
