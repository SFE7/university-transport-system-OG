<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Membre;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Trajet;
use Illuminate\Pagination\LengthAwarePaginator;

class ReservationService
{
    public function getMyReservations(Membre $actor): LengthAwarePaginator
    {
        return Reservation::where('membre_id', $actor->id)
            ->with(['trajet.conducteur'])
            ->paginate(15);
    }

    public function create(array $data, Membre $actor): Reservation
    {
        $trajet = Trajet::findOrFail($data['trajet_id']);

        abort_if($trajet->available_seats < 1, 422);

        $reservation = Reservation::create([
            'membre_id' => $actor->id,
            'trajet_id' => $data['trajet_id'],
            'status' => 'pending',
        ]);

        $trajet->decrement('available_seats');

        Notification::create([
            'membre_id' => $trajet->membre_id,
            'message' => 'Nouvelle demande de réservation sur votre trajet.',
            'type' => 'reservation_pending',
        ]);

        return $reservation;
    }

    public function accept(Reservation $reservation, Membre $actor): Reservation
    {
        abort_if($reservation->trajet->membre_id !== $actor->id, 403);

        $reservation->status = 'accepted';
        $reservation->save();

        Notification::create([
            'membre_id' => $reservation->membre_id,
            'message' => 'Votre réservation a été acceptée.',
            'type' => 'reservation_accepted',
        ]);

        return $reservation;
    }

    public function refuse(Reservation $reservation, Membre $actor): Reservation
    {
        abort_if($reservation->trajet->membre_id !== $actor->id, 403);

        $reservation->status = 'refused';
        $reservation->save();

        $reservation->trajet->increment('available_seats');

        Notification::create([
            'membre_id' => $reservation->membre_id,
            'message' => 'Votre réservation a été refusée.',
            'type' => 'reservation_refused',
        ]);

        return $reservation;
    }

    public function cancel(Reservation $reservation, Membre $actor): Reservation
    {
        abort_if($reservation->membre_id !== $actor->id, 403);

        $previousStatus = $reservation->status;

        $reservation->status = 'cancelled';
        $reservation->save();

        if (in_array($previousStatus, ['pending', 'accepted'])) {
            $reservation->trajet->increment('available_seats');
        }

        Notification::create([
            'membre_id' => $reservation->trajet->membre_id,
            'message' => 'Une réservation a été annulée par le passager.',
            'type' => 'reservation_cancelled',
        ]);

        return $reservation;
    }
}
