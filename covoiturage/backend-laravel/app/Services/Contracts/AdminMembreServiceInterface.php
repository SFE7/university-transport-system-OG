<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Membre;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminMembreServiceInterface
{
    public function getAll(): LengthAwarePaginator;

    public function getOne(int $id): Membre;

    public function updateRole(Membre $membre, string $role): Membre;

    public function toggleActive(Membre $membre): Membre;

    public function toggleSuspend(Membre $membre): Membre;

    public function ban(Membre $membre): Membre;

    public function delete(Membre $membre): void;
}
