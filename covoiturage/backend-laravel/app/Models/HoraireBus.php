<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoraireBus extends Model
{
    use HasFactory;

    protected $table = 'horaires_bus';

    protected $fillable = [
        'ligne_bus_id',
        'chauffeur_id',
        'departure_time',
        'days',
        'is_active',
    ];

    protected $casts = [
        'days' => 'array',
        'is_active' => 'boolean',
    ];

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(LigneBus::class);
    }

    public function chauffeur(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'chauffeur_id');
    }
}
