<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Membre;
use Illuminate\Support\Collection;

interface ChauffeurServiceInterface
{
    public function getAll(): Collection;

    public function create(array $data): Membre;

    public function getOne(int $id): Membre;

    public function delete(Membre $chauffeur): void;
}
