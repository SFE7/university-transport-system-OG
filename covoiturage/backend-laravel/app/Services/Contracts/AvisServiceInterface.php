<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Avis;
use App\Models\Membre;
use Illuminate\Pagination\LengthAwarePaginator;

interface AvisServiceInterface
{
    public function getOne(int $id): Avis;

    public function getByConducteur(int $conducteurId): LengthAwarePaginator;

    public function getAll(): LengthAwarePaginator;

    public function create(array $data, Membre $actor): Avis;

    public function update(Avis $avis, array $data, Membre $actor): Avis;

    public function delete(Avis $avis, Membre $actor): void;
}
