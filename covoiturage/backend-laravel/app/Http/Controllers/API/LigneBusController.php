<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLigneBusRequest;
use App\Http\Requests\UpdateLigneBusRequest;
use App\Http\Resources\LigneBusResource;
use App\Http\Resources\HoraireBusResource;
use App\Services\Contracts\LigneBusServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LigneBusController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly LigneBusServiceInterface $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $lignes = $this->service->getAll();

        return $this->success(LigneBusResource::collection($lignes)->response()->getData(true));
    }

    public function show(int $id): JsonResponse
    {
        $ligne = $this->service->getOne($id);

        return $this->success(new LigneBusResource($ligne));
    }

    public function store(StoreLigneBusRequest $request): JsonResponse
    {
        $ligne = $this->service->create($request->validated());

        return $this->success(new LigneBusResource($ligne), 'Ligne creee', 201);
    }

    public function update(UpdateLigneBusRequest $request, int $id): JsonResponse
    {
        $ligne = $this->service->getOne($id);
        $ligne = $this->service->update($ligne, $request->validated());

        return $this->success(new LigneBusResource($ligne), 'Ligne mise a jour');
    }

    public function toggleActive(int $id): JsonResponse
    {
        $ligne = $this->service->getOne($id);
        $ligne = $this->service->toggleActive($ligne);

        return $this->success(new LigneBusResource($ligne), 'Statut mis à jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $ligne = $this->service->getOne($id);
        $this->service->delete($ligne);

        return $this->success(null, 'Ligne supprimee');
    }

    public function schedules(int $id, Request $request): JsonResponse
    {
        $schedules = $this->service->getSchedules($id, $request->query('day'));

        return $this->success(HoraireBusResource::collection($schedules)->response()->getData(true));
    }
}
