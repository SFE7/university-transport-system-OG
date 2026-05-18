<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ChauffeurCredentialsMail;
use App\Models\DocumentSoumis;
use App\Models\Membre;
use App\Models\Notification;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function registerBasic(array $data): Membre
    {
        $membre = Membre::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return $membre->fresh();
    }

    public function register(array $data, string $type): Membre
    {
        $role = $type === 'conducteur' ? 'conducteur' : 'membre';
        $accountType = $type === 'conducteur' ? null : $type;

        $membre = Membre::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'phone' => $data['phone'] ?? null,
            'account_type' => $accountType,
        ]);

        if ($type === 'etudiant') {
            $path = $this->storeDocument($data['carte_etudiante'], 'etudiant');
            $membre->carte_etudiante_path = $path;
            $membre->save();

            DocumentSoumis::create([
                'membre_id' => $membre->id,
                'type' => 'carte_etudiante',
                'file_path' => $path,
                'status' => 'en_attente',
            ]);
        }

        if ($type === 'professionnel') {
            $path = $this->storeDocument($data['carte_identite'], 'professionnel');
            DocumentSoumis::create([
                'membre_id' => $membre->id,
                'type' => 'carte_identite',
                'file_path' => $path,
                'status' => 'en_attente',
            ]);
        }

        if ($type === 'conducteur') {
            $permisPath = $this->storeDocument($data['permis_conduire'], 'conducteur');
            $carteGrisePath = $this->storeDocument($data['carte_grise'], 'conducteur');

            DocumentSoumis::create([
                'membre_id' => $membre->id,
                'type' => 'permis_conduire',
                'file_path' => $permisPath,
                'status' => 'en_attente',
            ]);

            DocumentSoumis::create([
                'membre_id' => $membre->id,
                'type' => 'carte_grise',
                'file_path' => $carteGrisePath,
                'status' => 'en_attente',
            ]);
        }

        return $membre->fresh();
    }

    public function authenticate(array $data): ?Membre
    {
        $membre = Membre::where('email', $data['email'])->first();

        if (! $membre || ! Hash::check($data['password'], $membre->password)) {
            return null;
        }

        return $membre;
    }

    public function changePassword(Membre $membre, array $data): void
    {
        if (! Hash::check($data['current_password'], $membre->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mot de passe actuel incorrect',
            ]);
        }

        $membre->password = Hash::make($data['password']);
        $membre->save();
    }

    public function sendChauffeurCredentials(Membre $chauffeur): void
    {
        abort_if($chauffeur->role !== 'chauffeur_bus', 422);

        $plainPassword = Str::random(12);
        $chauffeur->password = Hash::make($plainPassword);
        $chauffeur->save();

        Mail::to($chauffeur->email)->send(
            new ChauffeurCredentialsMail($chauffeur->email, $plainPassword, $chauffeur->name)
        );

        Notification::create([
            'membre_id' => $chauffeur->id,
            'message' => 'Vos identifiants chauffeurs ont ete envoyes par email.',
            'type' => 'credentials_sent',
        ]);
    }

    private function storeDocument(object $file, string $folder): string
    {
        return Storage::disk('public')->putFile("documents/{$folder}", $file);
    }
}
