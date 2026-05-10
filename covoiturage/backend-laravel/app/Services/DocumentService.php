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
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentSoumis;
use App\Models\Membre;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

class DocumentService
{
    public function getPending(): Collection
    {
        return DocumentSoumis::with(['membre', 'reviewer'])
            ->where('status', 'en_attente')
            ->orderByDesc('created_at')
            ->get();
    }

    public function approve(DocumentSoumis $doc, Membre $admin): DocumentSoumis
    {
        $doc->status = 'approuve';
        $doc->reviewed_by = $admin->id;
        $doc->reviewed_at = now();
        $doc->rejection_reason = null;
        $doc->save();

        $membre = $doc->membre;
        $membre->has_verified_documents = true;
        if ($doc->type === 'carte_etudiante') {
            $membre->carte_etudiante_path = $doc->file_path;
        }
        $membre->save();

        Notification::create([
            'membre_id' => $membre->id,
            'message' => 'Votre document a ete approuve',
            'type' => 'document_approved',
        ]);

        return $doc->fresh(['membre', 'reviewer']);
    }

    public function reject(DocumentSoumis $doc, Membre $admin, string $reason): DocumentSoumis
    {
        $doc->status = 'rejete';
        $doc->rejection_reason = $reason;
        $doc->reviewed_by = $admin->id;
        $doc->reviewed_at = now();
        $doc->save();

        Notification::create([
            'membre_id' => $doc->membre_id,
            'message' => 'Votre document a ete rejete: ' . $reason,
            'type' => 'document_rejected',
        ]);

        return $doc->fresh(['membre', 'reviewer']);
    }
}
