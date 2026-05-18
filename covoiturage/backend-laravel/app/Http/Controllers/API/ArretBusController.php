<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArretBusRequest;
use App\Models\ArretBus;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ArretBusController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreArretBusRequest $request): JsonResponse
    {
        $arret = ArretBus::create($request->validated());

        return $this->success($arret, 'Arret cree', 201);
    }

    public function update(StoreArretBusRequest $request, int $id): JsonResponse
    {
        $arret = ArretBus::findOrFail($id);
        $arret->update($request->validated());

        return $this->success($arret, 'Arret mis a jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $arret = ArretBus::findOrFail($id);
        $arret->delete();

        return $this->success(null, 'Arret supprime');
    }
}
