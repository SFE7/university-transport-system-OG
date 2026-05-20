<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\Membre;
use App\Services\NotificationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly NotificationService $service
    ) {}

    public function index(): JsonResponse
    {
        $notifications = $this->service->getMyNotifications(auth()->user());

        return $this->success(NotificationResource::collection($notifications)->response()->getData(true));
    }

    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification = $this->service->markAsRead($notification, auth()->user());

        return $this->success(new NotificationResource($notification), 'Notification marquée comme lue');
    }

    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $this->service->delete($notification, auth()->user());

        return $this->success(null, 'Notification supprimée');
    }

    public function broadcast(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'message' => 'required|string',
            'type' => 'required|string',
            'target_roles' => 'required|array',
            'target_roles.*' => 'string',
        ]);

        // Server-side hard block: never allow broadcasting to chauffeurs
        $targetRoles = array_values(array_filter(
            array_map('strtolower', $payload['target_roles']),
            fn (string $role) => $role !== 'chauffeur'
        ));

        $sent = 0;

        if (!empty($targetRoles)) {
            $membres = Membre::query()
                ->where(function ($query) use ($targetRoles): void {
                    foreach ($targetRoles as $role) {
                        if ($role === 'membre') {
                            $query->orWhere('role', 'membre');
                            continue;
                        }

                        if ($role === 'conducteur') {
                            $query->orWhere('role', 'conducteur');
                            continue;
                        }

                        $query->orWhere(function ($subQuery) use ($role): void {
                            $subQuery->where('role', 'membre')
                                ->where('account_type', $role);
                        });
                    }
                })
                ->where('role', '!=', 'chauffeur')
                ->get(['id', 'role', 'account_type']);

            foreach ($membres as $membre) {
                Notification::create([
                    'membre_id' => $membre->id,
                    'message' => $payload['message'],
                    'type' => $payload['type'],
                    'target_role' => $membre->account_type ?: $membre->role,
                    'is_read' => false,
                ]);
                $sent++;
            }
        }

        return $this->success(['sent_count' => $sent], 'Notifications envoyées');
    }
}
