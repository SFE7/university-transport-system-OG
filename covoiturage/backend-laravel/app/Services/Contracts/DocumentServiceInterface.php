<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\DocumentSoumis;
use App\Models\Membre;
use Illuminate\Database\Eloquent\Collection;

interface DocumentServiceInterface
{
    public function getOne(int $id): DocumentSoumis;

    public function getPending(): Collection;

    public function getForMembre(Membre $membre): Collection;

    public function requiredDocumentTypes(Membre $membre): array;

    public function submit(Membre $membre, array $files): Collection;

    public function approve(DocumentSoumis $doc, Membre $admin): DocumentSoumis;

    public function reject(DocumentSoumis $doc, Membre $admin, array $data): DocumentSoumis;
}
