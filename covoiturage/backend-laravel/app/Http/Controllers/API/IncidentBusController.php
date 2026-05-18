<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentBusRequest;
use App\Http\Resources\IncidentBusResource;
use App\Services\Contracts\IncidentBusServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentBusController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly IncidentBusServiceInterface $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $incidents = $this->service->getAll();

        return $this->success(IncidentBusResource::collection($incidents)->response()->getData(true));
    }

    public function store(StoreIncidentBusRequest $request): JsonResponse
    {
        $incident = $this->service->create($request->validated(), auth()->user());

        return $this->success(new IncidentBusResource($incident), 'Incident signale', 201);
    }

    public function resolve(int $id, Request $request): JsonResponse
    {
        $incident = $this->service->getOne($id);
        $incident = $this->service->resolve($incident, auth()->user());

        return $this->success(new IncidentBusResource($incident), 'Incident resolu');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $incident = $this->service->getOne($id);
        $this->service->delete($incident, auth()->user());

        return $this->success(null, 'Incident supprime');
    }
}
