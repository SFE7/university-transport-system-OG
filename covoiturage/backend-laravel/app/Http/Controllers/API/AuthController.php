<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterConducteurRequest;
use App\Http\Requests\RegisterEtudiantRequest;
use App\Http\Requests\RegisterProfessionnelRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\MembreResource;
use App\Models\Membre;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:membres',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:membre,conducteur',
        ]);

        $membre = Membre::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

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
        $membre = $this->authService->register($request->validated(), 'etudiant');

        return $this->success([
            'token' => $membre->createToken('api_token')->plainTextToken,
            'membre' => new MembreResource($membre),
        ], 'Inscription réussie', 201);
    }

    public function registerProfessionnel(RegisterProfessionnelRequest $request): JsonResponse
    {
        $membre = $this->authService->register($request->validated(), 'professionnel');

        return $this->success([
            'token' => $membre->createToken('api_token')->plainTextToken,
            'membre' => new MembreResource($membre),
        ], 'Inscription réussie', 201);
    }

    public function registerConducteur(RegisterConducteurRequest $request): JsonResponse
    {
        $membre = $this->authService->register($request->validated(), 'conducteur');

        return $this->success([
            'token' => $membre->createToken('api_token')->plainTextToken,
            'membre' => new MembreResource($membre),
        ], 'Inscription réussie', 201);
    }

    public function changePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated());

        return $this->success(null, 'Mot de passe mis à jour');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $membre = Membre::where('email', $validated['email'])->first();

        if (!$membre || !Hash::check($validated['password'], $membre->password)) {
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
}
