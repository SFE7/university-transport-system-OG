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
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ReservationService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $reservations = $this->service->getMyReservations($request->user());

        return $this->success(ReservationResource::collection($reservations)->response()->getData(true));
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $reservation = $this->service->create($request->validated(), $request->user());

        return $this->success(new ReservationResource($reservation), 'Réservation créée', 201);
    }

    public function mesDemandes(Request $request): JsonResponse
    {
        $reservations = $this->service->getPendingDemandesForConducteur($request->user());

        return $this->success(ReservationResource::collection($reservations)->response()->getData(true));
    }

    public function show(int $id): JsonResponse
    {
        $reservation = Reservation::with(['trajet'])->findOrFail($id);

        return $this->success(new ReservationResource($reservation));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);
        $this->service->cancel($reservation, $request->user());

        return $this->success(null, 'Réservation annulée');
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $reservation = Reservation::with(['trajet'])->findOrFail($id);
        $reservation = $this->service->accept($reservation, $request->user());

        return $this->success(new ReservationResource($reservation), 'Réservation acceptée');
    }

    public function refuse(Request $request, int $id): JsonResponse
    {
        $reservation = Reservation::with(['trajet'])->findOrFail($id);
        $reservation = $this->service->refuse($reservation, $request->user());

        return $this->success(new ReservationResource($reservation), 'Réservation refusée');
    }
}
