<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfilRequest;
use App\Http\Requests\UpdateVehiculeRequest;
use App\Http\Resources\MembreResource;
use App\Services\ProfilService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ProfilService $service) {}

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
        $user = $request->user();
        if ($user->role !== 'conducteur') {
            return $this->error('Accès refusé', 403);
        }

        $photo = $request->file('photo');
        $vehicule = $this->service->updateVehicule($user, $request->validated(), $photo);

        return $this->success($vehicule, 'Véhicule mis à jour');
    }
}
