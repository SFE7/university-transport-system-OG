<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSignalementRequest;
use App\Http\Requests\UpdateSignalementRequest;
use App\Http\Resources\SignalementResource;
use App\Services\Contracts\SignalementServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalementController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly SignalementServiceInterface $service) {}

    public function store(StoreSignalementRequest $request): JsonResponse
    {
        $signalement = $this->service->create($request->validated(), $request->user());

        return $this->success(new SignalementResource($signalement), 'Signalement créé', 201);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only('status');
        $list = $this->service->getAll($filters);

        return $this->success(SignalementResource::collection($list)->response()->getData(true));
    }

    public function updateStatus(UpdateSignalementRequest $request, int $id): JsonResponse
    {
        $signalement = $this->service->getOne($id);
        $signalement = $this->service->updateStatus($signalement, (string) $request->status);

        return $this->success(new SignalementResource($signalement), 'Statut mis à jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $signalement = $this->service->getOne($id);
        $this->service->delete($signalement);

        return $this->success(null, 'Signalement supprimé');
    }
}
