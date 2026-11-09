<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\StatistiquesService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class StatistiquesController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly StatistiquesService $service
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->service->getStats());
    }
}
