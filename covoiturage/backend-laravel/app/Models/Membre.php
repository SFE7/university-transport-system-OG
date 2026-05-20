<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Membre extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_banned',
        'account_type',
        'carte_etudiante_path',
        'has_verified_documents',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function trajets(): HasMany
    {
        return $this->hasMany(Trajet::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function avisGiven(): HasMany
    {
        return $this->hasMany(Avis::class, 'reviewer_id');
    }

    public function avisReceived(): HasMany
    {
        return $this->hasMany(Avis::class, 'conducteur_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function busPosition(): HasOne
    {
        return $this->hasOne(BusPosition::class, 'chauffeur_id');
    }

    public function horaires(): HasMany
    {
        return $this->hasMany(HoraireBus::class, 'chauffeur_id');
    }

    public function documentsSoumis(): HasMany
    {
        return $this->hasMany(DocumentSoumis::class, 'membre_id');
    }

    public function signalementsReporter(): HasMany
    {
        return $this->hasMany(Signalement::class, 'reporter_id');
    }

    public function signalementsReported(): HasMany
    {
        return $this->hasMany(Signalement::class, 'reported_id');
    }

    public function vehicule(): HasOne
    {
        return $this->hasOne(Vehicule::class, 'conducteur_id');
    }
}
