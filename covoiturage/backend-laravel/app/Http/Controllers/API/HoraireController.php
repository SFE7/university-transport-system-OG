<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHoraireBusRequest;
use App\Http\Requests\UpdateHoraireBusRequest;
use App\Http\Resources\HoraireBusResource;
use App\Services\Contracts\HoraireBusServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class HoraireController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly HoraireBusServiceInterface $service
    ) {}

    public function index(): JsonResponse
    {
        $horaires = $this->service->getAll();

        return $this->success(HoraireBusResource::collection($horaires)->response()->getData(true));
    }

    public function store(StoreHoraireBusRequest $request): JsonResponse
    {
        $horaire = $this->service->create($request->validated());

        return $this->success(new HoraireBusResource($horaire), 'Horaire cree', 201);
    }

    public function update(UpdateHoraireBusRequest $request, int $id): JsonResponse
    {
        $horaire = $this->service->getOne($id);
        $horaire = $this->service->update($horaire, $request->validated());

        return $this->success(new HoraireBusResource($horaire), 'Horaire mis a jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $horaire = $this->service->getOne($id);
        $this->service->delete($horaire);

        return $this->success(null, 'Horaire supprime');
    }
}
