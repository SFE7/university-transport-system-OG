<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArretBusRequest;
use App\Http\Requests\UpdateArretBusRequest;
use App\Http\Resources\ArretBusResource;
use App\Services\Contracts\ArretBusServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ArretBusController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ArretBusServiceInterface $service
    ) {}

    public function store(StoreArretBusRequest $request): JsonResponse
    {
        $arret = $this->service->create($request->validated());

        return $this->success(new ArretBusResource($arret), 'Arret cree', 201);
    }

    public function update(UpdateArretBusRequest $request, int $id): JsonResponse
    {
        $arret = $this->service->getOne($id);
        $arret = $this->service->update($arret, $request->validated());

        return $this->success(new ArretBusResource($arret), 'Arret mis a jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $arret = $this->service->getOne($id);
        $this->service->delete($arret);

        return $this->success(null, 'Arret supprime');
    }
}
