<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChauffeurRequest;
use App\Http\Resources\MembreResource;
use App\Services\Contracts\ChauffeurServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ChauffeurController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ChauffeurServiceInterface $service
    ) {}

    public function index(): JsonResponse
    {
        $chauffeurs = $this->service->getAll();

        return $this->success(MembreResource::collection($chauffeurs)->response()->getData(true));
    }

    public function store(StoreChauffeurRequest $request): JsonResponse
    {
        $chauffeur = $this->service->create($request->validated());

        return $this->success(new MembreResource($chauffeur), 'Chauffeur cree', 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $chauffeur = $this->service->getOne($id);
        $this->service->delete($chauffeur);

        return $this->success(null, 'Chauffeur supprime');
    }
}
