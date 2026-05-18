<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfilRequest;
use App\Http\Requests\UpdateVehiculeRequest;
use App\Http\Resources\MembreResource;
use App\Http\Resources\VehiculeResource;
use App\Services\Contracts\ProfilServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ProfilController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ProfilServiceInterface $service) {}

    public function show(int $id): JsonResponse
    {
        $membre = $this->service->getProfile($id);

        if (! $membre) {
            return $this->error('Membre introuvable', 404);
        }

        return $this->success(new MembreResource($membre));
    }

    public function update(UpdateProfilRequest $request): JsonResponse
    {
        $membre = $this->service->updateProfile($request->user(), $request->validated());

        return $this->success(new MembreResource($membre), 'Profil mis à jour');
    }

    public function updateVehicule(UpdateVehiculeRequest $request): JsonResponse
    {
        $vehicule = $this->service->updateVehicule($request->user(), $request->validated());

        return $this->success(new VehiculeResource($vehicule), 'Véhicule mis à jour');
    }
}
