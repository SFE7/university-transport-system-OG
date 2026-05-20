<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentBusRequest;
use App\Models\IncidentBus;
use App\Services\IncidentBusService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentBusController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly IncidentBusService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $incidents = $this->service->getAll();

        return $this->success($incidents);
    }

    public function store(StoreIncidentBusRequest $request): JsonResponse
    {
        $incident = $this->service->create($request->validated(), auth()->user());

        return $this->success($incident, 'Incident signale', 201);
    }

    public function resolve(int $id, Request $request): JsonResponse
    {
        $incident = IncidentBus::findOrFail($id);
        $incident = $this->service->resolve($incident, auth()->user());

        return $this->success($incident, 'Incident resolu');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $incident = IncidentBus::findOrFail($id);
        $this->service->delete($incident, auth()->user());

        return $this->success(null, 'Incident supprime');
    }
}
