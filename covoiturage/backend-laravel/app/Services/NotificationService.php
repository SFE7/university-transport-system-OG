<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Membre;
use App\Models\Notification;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function getMyNotifications(Membre $actor): LengthAwarePaginator
    {
        return Notification::where('membre_id', $actor->id)
            ->orderBy('is_read', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);
    }

    public function markAsRead(Notification $notification, Membre $actor): Notification
    {
        abort_if($notification->membre_id !== $actor->id, 403);

        $notification->is_read = true;
        $notification->save();

        return $notification;
    }
}
