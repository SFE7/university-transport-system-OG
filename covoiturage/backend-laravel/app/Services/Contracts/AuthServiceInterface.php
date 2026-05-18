<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Membre;

interface AuthServiceInterface
{
    public function registerBasic(array $data): Membre;

    public function register(array $data, string $type): Membre;

    public function authenticate(array $data): ?Membre;

    public function changePassword(Membre $membre, array $data): void;

    public function sendChauffeurCredentials(Membre $chauffeur): void;
}
