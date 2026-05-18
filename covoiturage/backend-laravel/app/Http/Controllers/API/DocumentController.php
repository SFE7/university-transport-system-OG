<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentSoumisResource;
use App\Models\Membre;
use App\Services\Contracts\DocumentServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly DocumentServiceInterface $service) {}

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

        try {
            $this->service->submit($user, $request->all());
        } catch (ValidationException $exception) {
            return $this->error($exception->errors()['documents'][0] ?? 'Validation error', 422);
        }

        return $this->success($this->buildStatusPayload($user), 'Documents soumis avec succès');
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $doc = $this->service->getOne($id);
        $doc = $this->service->approve($doc, $request->user());

        return $this->success(new DocumentSoumisResource($doc), 'Document approuvé');
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $doc = $this->service->getOne($id);
        $doc = $this->service->reject($doc, $request->user(), $request->only('reason'));

        return $this->success(new DocumentSoumisResource($doc), 'Document rejeté');
    }

    public function preview(int $id)
    {
        $doc = $this->service->getOne($id);
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
