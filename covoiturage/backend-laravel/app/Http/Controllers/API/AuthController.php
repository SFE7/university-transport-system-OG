<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterMembreRequest;
use App\Http\Requests\RegisterConducteurRequest;
use App\Http\Requests\RegisterEtudiantRequest;
use App\Http\Requests\RegisterProfessionnelRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\MembreResource;
use App\Models\Membre;
use App\Services\Contracts\AuthServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    public function register(RegisterMembreRequest $request): JsonResponse
    {
        $membre = $this->authService->registerBasic($request->validated());

        $token = $membre->createToken('api_token')->plainTextToken;

        return $this->success(
            [
                'token' => $token,
                'membre' => new MembreResource($membre),
            ],
            'Registered successfully',
            201
        );
    }

    public function registerEtudiant(RegisterEtudiantRequest $request): JsonResponse
    {
        try {
            $membre = $this->authService->register($request->validated(), 'etudiant');
            $token = $this->issueToken($membre);

            return $this->success([
                'token' => $token,
                'membre' => new MembreResource($membre),
            ], 'Inscription réussie', 201);
        } catch (\Throwable $e) {
            Log::error('Registration error', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function registerProfessionnel(RegisterProfessionnelRequest $request): JsonResponse
    {
        try {
            $membre = $this->authService->register($request->validated(), 'professionnel');
            $token = $this->issueToken($membre);

            return $this->success([
                'token' => $token,
                'membre' => new MembreResource($membre),
            ], 'Inscription réussie', 201);
        } catch (\Throwable $e) {
            Log::error('Registration error', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function registerConducteur(RegisterConducteurRequest $request): JsonResponse
    {
        try {
            $membre = $this->authService->register($request->validated(), 'conducteur');
            $token = $this->issueToken($membre);

            return $this->success([
                'token' => $token,
                'membre' => new MembreResource($membre),
            ], 'Inscription réussie', 201);
        } catch (\Throwable $e) {
            Log::error('Registration error', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function changePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated());

        return $this->success(null, 'Mot de passe mis à jour');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $membre = $this->authService->authenticate($validated);

        if (! $membre) {
            return $this->error('Invalid credentials', 401);
        }

        $token = $membre->createToken('api_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'membre' => new MembreResource($membre),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new MembreResource($request->user()));
    }

    private function issueToken(Membre $membre): string
    {
        try {
            return $membre->createToken('api_token')->plainTextToken;
        } catch (\Throwable $e) {
            Log::warning('Sanctum token creation failed, using manual fallback', [
                'membre_id' => $membre->id,
                'exception' => $e->getMessage(),
            ]);

            $plainToken = Str::random(40);
            $tokenId = DB::table('personal_access_tokens')->insertGetId([
                'tokenable_type' => $membre::class,
                'tokenable_id' => $membre->id,
                'name' => 'api_token',
                'token' => hash('sha256', $plainToken),
                'abilities' => json_encode(['*']),
                'last_used_at' => null,
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $tokenId . '|' . $plainToken;
        }
    }
}
