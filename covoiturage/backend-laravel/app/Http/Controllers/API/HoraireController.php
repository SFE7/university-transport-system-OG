<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHoraireBusRequest;
use App\Models\HoraireBus;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class HoraireController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreHoraireBusRequest $request): JsonResponse
    {
        $horaire = HoraireBus::create($request->validated());

        return $this->success($horaire, 'Horaire cree', 201);
    }

    public function update(StoreHoraireBusRequest $request, int $id): JsonResponse
    {
        $horaire = HoraireBus::findOrFail($id);
        $horaire->update($request->validated());

        return $this->success($horaire, 'Horaire mis a jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $horaire = HoraireBus::findOrFail($id);
        $horaire->delete();

        return $this->success(null, 'Horaire supprime');
    }
}
