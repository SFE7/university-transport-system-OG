<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\ReservationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ReservationService $service
    ) {}

    public function index(): JsonResponse
    {
        $reservations = $this->service->getMyReservations(auth()->user());

        return $this->success(ReservationResource::collection($reservations)->response()->getData(true));
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $reservation = $this->service->create($request->validated(), auth()->user());

        return $this->success(new ReservationResource($reservation), 'Réservation créée', 201);
    }

    public function show(int $id): JsonResponse
    {
        $reservation = Reservation::with(['trajet'])->findOrFail($id);

        return $this->success(new ReservationResource($reservation));
    }

    public function destroy(int $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);
        $this->service->cancel($reservation, auth()->user());

        return $this->success(null, 'Réservation annulée');
    }

    public function accept(int $id): JsonResponse
    {
        $reservation = Reservation::with(['trajet'])->findOrFail($id);
        $reservation = $this->service->accept($reservation, auth()->user());

        return $this->success(new ReservationResource($reservation), 'Réservation acceptée');
    }

    public function refuse(int $id): JsonResponse
    {
        $reservation = Reservation::with(['trajet'])->findOrFail($id);
        $reservation = $this->service->refuse($reservation, auth()->user());

        return $this->success(new ReservationResource($reservation), 'Réservation refusée');
    }
}
