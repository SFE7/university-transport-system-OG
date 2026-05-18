<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdminMembreRequest;
use App\Http\Resources\MembreResource;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\AdminMembreServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMembreController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AdminMembreServiceInterface $service
        , private readonly AuthServiceInterface $authService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $membres = $this->service->getAll();

        return $this->success(MembreResource::collection($membres)->response()->getData(true));
    }

    public function show(int $id): JsonResponse
    {
        $membre = $this->service->getOne($id);

        return $this->success(new MembreResource($membre));
    }

    public function updateRole(UpdateAdminMembreRequest $request, int $id): JsonResponse
    {
        $membre = $this->service->getOne($id);
        $membre = $this->service->updateRole($membre, (string) $request->validated()['role']);

        return $this->success(new MembreResource($membre), 'Role mis a jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $membre = $this->service->getOne($id);
        $this->service->delete($membre);

        return $this->success(null, 'Membre supprime');
    }

    public function sendCredentials(int $id): JsonResponse
    {
        $membre = $this->service->getOne($id);
        abort_if($membre->role !== 'chauffeur_bus', 422);

        $this->authService->sendChauffeurCredentials($membre);

        return $this->success(null, 'Identifiants envoyés par email');
    }

    public function toggleSuspend(int $id): JsonResponse
    {
        $membre = $this->service->getOne($id);
        $membre = $this->service->toggleSuspend($membre);

        return $this->success(new MembreResource($membre));
    }

    public function ban(int $id): JsonResponse
    {
        $membre = $this->service->getOne($id);
        $membre = $this->service->ban($membre);

        return $this->success(new MembreResource($membre), 'Membre banni');
    }
}
