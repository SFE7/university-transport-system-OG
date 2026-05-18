<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLigneBusRequest;
use App\Services\LigneBusService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LigneBusController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly LigneBusService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $lignes = $this->service->getAll();

        return $this->success($lignes);
    }

    public function show(int $id): JsonResponse
    {
        $ligne = $this->service->getOne($id);

        return $this->success($ligne);
    }

    public function store(StoreLigneBusRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! isset($data['color']) || $data['color'] === '') {
            $data['color'] = '#00c853';
        }
        $arrets = $data['arrets'] ?? null;
        unset($data['arrets']);

        $ligne = $this->service->create($data);

        if (is_array($arrets) && count($arrets)) {
            $ligne->arrets()->createMany($arrets);
            $ligne->load(['arrets' => function ($q) { $q->orderBy('order'); }]);
        }

        return $this->success($ligne, 'Ligne creee', 201);
    }

    public function update(StoreLigneBusRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        if (array_key_exists('color', $data) && $data['color'] === '') {
            $data['color'] = '#00c853';
        }
        $arrets = $data['arrets'] ?? null;
        unset($data['arrets']);

        $ligne = $this->service->getOne($id);
        $ligne = $this->service->update($ligne, $data);

        if (is_array($arrets)) {
            // remove existing stops and recreate
            $ligne->arrets()->delete();
            if (count($arrets)) {
                $ligne->arrets()->createMany($arrets);
            }
            $ligne->load(['arrets' => function ($q) { $q->orderBy('order'); }]);
        }

        return $this->success($ligne, 'Ligne mise a jour');
    }

    public function toggleActive(int $id): JsonResponse
    {
        $ligne = $this->service->getOne($id);
        $ligne = $this->service->toggleActive($ligne);

        return $this->success($ligne, 'Statut mis à jour');
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

        return $this->success($schedules);
    }
}
