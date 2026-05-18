<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembreResource;
use App\Models\Membre;
use App\Services\AuthService;
use App\Services\AdminMembreService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMembreController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AdminMembreService $service
        , private readonly AuthService $authService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $membres = $this->service->getAll();

        return $this->success($membres);
    }

    public function show(int $id): JsonResponse
    {
        $membre = $this->service->getOne($id);

        return $this->success($membre);
    }

    public function updateRole(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:membre,conducteur,chauffeur_bus,admin',
        ]);

        $membre = $this->service->getOne($id);
        $membre = $this->service->updateRole($membre, (string) $request->role);

        return $this->success($membre, 'Role mis a jour');
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
