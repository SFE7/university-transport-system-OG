<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentSoumis;
use App\Models\Membre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function getPending(): Collection
    {
        return DocumentSoumis::with('membre')->where('status', 'en_attente')->get();
    }

    public function getForMembre(Membre $membre): Collection
    {
        return $membre->documentsSoumis()->with('membre')->latest()->get();
    }

    public function requiredDocumentTypes(Membre $membre): array
    {
        if (in_array($membre->role, ['admin', 'chauffeur_bus'], true)) {
            return [];
        }

        if ($membre->role === 'conducteur') {
            return ['permis_conduire', 'carte_grise'];
        }

        return match ($membre->account_type) {
            'professionnel' => ['carte_identite'],
            default => ['carte_etudiante'],
        };
    }

    public function submit(Membre $membre, array $files): Collection
    {
        foreach ($this->requiredDocumentTypes($membre) as $type) {
            $file = $files[$type] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = Storage::disk('public')->putFile('documents/' . $type, $file);

            DocumentSoumis::create([
                'membre_id' => $membre->id,
                'type' => $type,
                'file_path' => $path,
                'status' => 'en_attente',
            ]);

            if ($type === 'carte_etudiante') {
                $membre->carte_etudiante_path = $path;
                $membre->save();
            }
        }

        return $this->getForMembre($membre);
    }

    public function approve(DocumentSoumis $doc, Membre $admin): DocumentSoumis
    {
        $doc->status = 'approuve';
        $doc->reviewed_by = $admin->id;
        $doc->reviewed_at = now();
        $doc->save();

        $membre = $doc->membre;
        $this->syncVerificationStatus($membre);

        if (method_exists($membre, 'notifications')) {
            $membre->notifications()->create([
                'type' => 'document_approved',
                'data' => json_encode(['message' => "Votre document a été approuvé"]),
            ]);
        }

        return $doc;
    }

    private function syncVerificationStatus(Membre $membre): void
    {
        if (in_array($membre->role, ['admin', 'chauffeur_bus'], true)) {
            return;
        }

        $requiredTypes = $this->requiredDocumentTypes($membre);

        if ($requiredTypes === []) {
            return;
        }

        $approvedTypes = $membre->documentsSoumis()
            ->whereIn('type', $requiredTypes)
            ->where('status', 'approuve')
            ->pluck('type')
            ->unique()
            ->all();

        if (count($approvedTypes) === count($requiredTypes)) {
            $membre->has_verified_documents = true;
            $membre->save();
        }
    }

    public function reject(DocumentSoumis $doc, Membre $admin, string $reason): DocumentSoumis
    {
        $doc->status = 'rejete';
        $doc->rejection_reason = $reason;
        $doc->reviewed_by = $admin->id;
        $doc->reviewed_at = now();
        $doc->save();

        $membre = $doc->membre;
        if (method_exists($membre, 'notifications')) {
            $membre->notifications()->create([
                'type' => 'document_rejected',
                'data' => json_encode(['message' => "Votre document a été rejeté: $reason"]),
            ]);
        }

        return $doc;
    }
}
