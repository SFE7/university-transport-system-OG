<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentSoumis;
use App\Models\Membre;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function getPending()
    {
        return DocumentSoumis::with('membre')->where('status', 'en_attente')->get();
    }

    public function approve(DocumentSoumis $doc, Membre $admin): DocumentSoumis
    {
        $doc->status = 'approuve';
        $doc->reviewed_by = $admin->id;
        $doc->reviewed_at = now();
        $doc->save();

        $membre = $doc->membre;
        $membre->has_verified_documents = true;
        $membre->save();

        if (method_exists($membre, 'notifications')) {
            $membre->notifications()->create([
                'type' => 'document_approved',
                'data' => json_encode(['message' => "Votre document a été approuvé"]),
            ]);
        }

        return $doc;
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
