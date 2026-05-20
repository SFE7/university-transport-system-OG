<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvisRequest;
use App\Http\Resources\AvisResource;
use App\Services\AvisService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AvisController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AvisService $service
    ) {}

    public function index(int $membreId): JsonResponse
    {
        $avis = $this->service->getByConducteur($membreId);

        return $this->success(AvisResource::collection($avis)->response()->getData(true));
    }

    public function store(StoreAvisRequest $request): JsonResponse
    {
        $avis = $this->service->create($request->validated(), auth()->user());

        return $this->success(new AvisResource($avis), 'Avis créé', 201);
    }

    public function update(StoreAvisRequest $request, int $id): JsonResponse
    {
        $avis = \App\Models\Avis::findOrFail($id);

        // only reviewer can update
        if ($avis->reviewer_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();

        $avis->rating = $validated['rating'];
        $avis->comment = $validated['comment'] ?? null;
        $avis->save();

        return $this->success(new AvisResource($avis), 'Avis mis a jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $avis = \App\Models\Avis::findOrFail($id);

        // only reviewer can delete
        if ($avis->reviewer_id !== auth()->id()) {
            abort(403);
        }

        $avis->delete();

        return $this->success(null, 'Avis supprime');
    }
}
