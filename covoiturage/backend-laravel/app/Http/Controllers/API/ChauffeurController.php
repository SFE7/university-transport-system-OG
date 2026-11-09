<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembreResource;
use App\Models\Membre;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChauffeurController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $chauffeurs = Membre::query()
            ->where('role', 'chauffeur_bus')
            ->orderByDesc('created_at')
            ->get();

        return $this->success($chauffeurs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:membres,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $chauffeur = Membre::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'chauffeur_bus',
        ]);

        return $this->success(new MembreResource($chauffeur), 'Chauffeur cree', 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $chauffeur = Membre::findOrFail($id);
        abort_unless($chauffeur->role === 'chauffeur_bus', 404);

        $chauffeur->delete();

        return $this->success(null, 'Chauffeur supprime');
    }
}
