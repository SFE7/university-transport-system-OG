<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvisRequest;
use App\Http\Requests\UpdateAvisRequest;
use App\Http\Resources\AvisResource;
use App\Services\Contracts\AvisServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AvisController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AvisServiceInterface $service
    ) {}

    public function index(?int $membreId = null): JsonResponse
    {
        if ($membreId) {
            $avis = $this->service->getByConducteur($membreId);
            $payload = AvisResource::collection($avis)->response()->getData(true);
        } else {
            $avis = $this->service->getAll();
            $payload = AvisResource::collection($avis)->response()->getData(true);
        }

        return $this->success($payload);
    }

    public function store(StoreAvisRequest $request): JsonResponse
    {
        $avis = $this->service->create($request->validated(), auth()->user());

        return $this->success(new AvisResource($avis), 'Avis créé', 201);
    }

    public function update(UpdateAvisRequest $request, int $id): JsonResponse
    {
        $avis = $this->service->getOne($id);
        $avis = $this->service->update($avis, $request->validated(), $request->user());

        return $this->success(new AvisResource($avis), 'Avis mis a jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $avis = $this->service->getOne($id);
        $this->service->delete($avis, auth()->user());

        return $this->success(null, 'Avis supprime');
    }
}
