<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Membre;
use App\Models\Notification;
use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationServiceInterface
{
    public function getOne(int $id): Notification;

    public function getMyNotifications(Membre $actor): LengthAwarePaginator;

    public function markAsRead(Notification $notification, Membre $actor): Notification;
}
