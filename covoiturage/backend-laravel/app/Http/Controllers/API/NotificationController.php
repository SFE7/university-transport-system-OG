<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Traits\ApiResponseTrait;
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
}
