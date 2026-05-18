<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Membre;
use App\Models\Trajet;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

interface TrajetServiceInterface
{
    public function autoCompleteExpired(): void;

    public function getAll(array $filters = []): LengthAwarePaginator;

    public function getOne(int $id): Trajet;

    public function create(array $data, ?UploadedFile $carPhoto, Membre $actor): Trajet;

    public function update(Trajet $trajet, array $data, Membre $actor): Trajet;

    public function cancel(Trajet $trajet, Membre $actor): Trajet;

    public function getHistory(Membre $actor): LengthAwarePaginator;

    public function getMyTrajets(Membre $actor): LengthAwarePaginator;
}
