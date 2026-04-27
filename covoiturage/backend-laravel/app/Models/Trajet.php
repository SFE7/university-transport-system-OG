<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trajet extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_point',
        'arrival_point',
        'departure_time',
        'available_seats',
        'status',
        'membre_id',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
    ];

    public function conducteur(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'membre_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function avis(): HasMany
    {
        return $this->hasMany(Avis::class);
    }
}
