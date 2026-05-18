<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Membre;
use App\Models\Reservation;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReservationServiceInterface
{
    public function getOne(int $id): Reservation;

    public function getMyReservations(Membre $actor): LengthAwarePaginator;

    public function create(array $data, Membre $actor): Reservation;

    public function getPendingDemandesForConducteur(Membre $actor): LengthAwarePaginator;

    public function accept(Reservation $reservation, Membre $actor): Reservation;

    public function refuse(Reservation $reservation, Membre $actor): Reservation;

    public function cancel(Reservation $reservation, Membre $actor): Reservation;
}
