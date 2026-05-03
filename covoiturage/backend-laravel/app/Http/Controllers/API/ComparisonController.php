<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ComparisonService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ComparisonService $service
    ) {}

    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'departure' => 'required|string',
            'arrival' => 'required|string',
            'datetime' => 'required|date',
        ]);

        $result = $this->service->compare(
            (string) $request->departure,
            (string) $request->arrival,
            (string) $request->datetime
        );

        return $this->success($result);
    }
}
