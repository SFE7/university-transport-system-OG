<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrajetRequest;
use App\Http\Resources\TrajetResource;
use App\Services\TrajetService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrajetController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly TrajetService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $trajets = $this->service->getAll($filters);

        return $this->success(TrajetResource::collection($trajets)->response()->getData(true));
    }

    public function store(StoreTrajetRequest $request): JsonResponse
    {
        $trajet = $this->service->create($request->validated(), $request->file('car_photo'), $request->user());

        return $this->success(new TrajetResource($trajet), 'Trajet créé', 201);
    }

    public function show(int $id): JsonResponse
    {
        $trajet = $this->service->getOne($id);

        return $this->success(new TrajetResource($trajet));
    }

    public function update(StoreTrajetRequest $request, int $id): JsonResponse
    {
        $trajet = $this->service->getOne($id);
        $trajet = $this->service->update($trajet, $request->validated(), $request->user());

        return $this->success(new TrajetResource($trajet));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $trajet = $this->service->getOne($id);
        $this->service->cancel($trajet, $request->user());

        return $this->success(null, 'Trajet annulé');
    }

    public function history(Request $request): JsonResponse
    {
        $trajets = $this->service->getHistory($request->user());

        return $this->success(TrajetResource::collection($trajets)->response()->getData(true));
    }

    public function mesTrajets(Request $request): JsonResponse
    {
        $trajets = $this->service->getMyTrajets($request->user());

        return $this->success(TrajetResource::collection($trajets)->response()->getData(true));
    }
}
