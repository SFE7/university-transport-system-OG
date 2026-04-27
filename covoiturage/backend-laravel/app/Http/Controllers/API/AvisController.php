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
}
