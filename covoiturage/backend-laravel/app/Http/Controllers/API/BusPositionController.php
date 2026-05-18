<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBusPositionRequest;
use App\Http\Resources\BusPositionResource;
use App\Services\Contracts\BusPositionServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusPositionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly BusPositionServiceInterface $service
    ) {}

    public function update(UpdateBusPositionRequest $request): JsonResponse
    {
        $position = $this->service->updatePosition(auth()->user(), $request->validated());

        return $this->success(new BusPositionResource($position), 'Position mise a jour');
    }

    public function stopSharing(Request $request): JsonResponse
    {
        $position = $this->service->stopSharing(auth()->user());

        return $this->success(new BusPositionResource($position), 'Partage arrete');
    }

    public function index(Request $request): JsonResponse
    {
        $positions = $this->service->getActivePositions();

        return $this->success(BusPositionResource::collection($positions)->response()->getData(true));
    }
}
