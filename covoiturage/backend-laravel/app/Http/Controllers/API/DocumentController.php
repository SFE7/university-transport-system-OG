<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentSoumisResource;
use App\Models\DocumentSoumis;
use App\Models\Membre;
use App\Services\DocumentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly DocumentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user?->role === 'admin') {
            return $this->success(DocumentSoumisResource::collection($this->service->getPending()));
        }

        return $this->success($this->buildStatusPayload($user));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'carte_etudiante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'carte_identite' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'permis_conduire' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'carte_grise' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $files = array_filter($validated, static fn ($file): bool => $file instanceof UploadedFile);

        if ($files === []) {
            return $this->error("Au moins un document doit être soumis.", 422);
        }

        $this->service->submit($user, $files);

        return $this->success($this->buildStatusPayload($user), 'Documents soumis avec succès');
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $doc = DocumentSoumis::findOrFail($id);
        $doc = $this->service->approve($doc, $request->user());

        return $this->success(new DocumentSoumisResource($doc), 'Document approuvé');
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $doc = DocumentSoumis::findOrFail($id);
        $doc = $this->service->reject($doc, $request->user(), (string) $request->reason);

        return $this->success(new DocumentSoumisResource($doc), 'Document rejeté');
    }

    public function preview(int $id)
    {
        $doc = DocumentSoumis::findOrFail($id);
        $path = Storage::disk('public')->path($doc->file_path);

        abort_unless(is_file($path), 404, 'Fichier introuvable');

        return response()->file($path);
    }

    private function buildStatusPayload(Membre $user): array
    {
        $documents = $this->service->getForMembre($user);

        return [
            'role' => $user->role,
            'account_type' => $user->account_type,
            'has_verified_documents' => (bool) $user->has_verified_documents,
            'required_documents' => $this->service->requiredDocumentTypes($user),
            'documents' => DocumentSoumisResource::collection($documents),
        ];
    }
}
