<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSignalementRequest;
use App\Http\Resources\SignalementResource;
use App\Models\Signalement;
use App\Services\SignalementService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalementController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly SignalementService $service) {}

    public function store(StoreSignalementRequest $request): JsonResponse
    {
        $signalement = $this->service->create($request->validated(), $request->user());

        return $this->success(new SignalementResource($signalement), 'Signalement créé', 201);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only('status');
        $list = $this->service->getAll($filters);

        return $this->success(SignalementResource::collection($list));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:en_attente,traite,archive']);

        $signalement = Signalement::findOrFail($id);
        $signalement = $this->service->updateStatus($signalement, (string) $request->status);

        return $this->success(new SignalementResource($signalement), 'Statut mis à jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $signalement = Signalement::findOrFail($id);
        $this->service->delete($signalement);

        return $this->success(null, 'Signalement supprimé');
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Signalement;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalementController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly \App\Services\SignalementService $service
    ) {}

    public function store(\App\Http\Requests\StoreSignalementRequest $request): JsonResponse
    {
        $signalement = $this->service->create($request->validated(), $request->user());

        return $this->success(new \App\Http\Resources\SignalementResource($signalement->load(['reporter', 'reported'])), 'Signalement créé', 201);
    }

    public function index(Request $request): JsonResponse
    {
        $signalements = $this->service->getAll($request->all());

        return $this->success(\App\Http\Resources\SignalementResource::collection($signalements)->response()->getData(true));
    }

    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:en_attente,traite,archive',
        ]);

        $signalement = Signalement::with(['reporter', 'reported'])->findOrFail($id);
        $signalement = $this->service->updateStatus($signalement, $validated['status']);

        return $this->success(new \App\Http\Resources\SignalementResource($signalement), 'Statut mis à jour');
    }

    public function destroy(int $id): JsonResponse
    {
        $signalement = Signalement::findOrFail($id);
        $this->service->delete($signalement);

        return $this->success(null, 'Signalement supprimé');
    }
}
