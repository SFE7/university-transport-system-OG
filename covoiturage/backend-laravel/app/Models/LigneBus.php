<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LigneBus extends Model
{
    use HasFactory;

    protected $table = 'lignes_bus';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function arrets(): HasMany
    {
        return $this->hasMany(ArretBus::class)->orderBy('order');
    }

    public function horaires(): HasMany
    {
        return $this->hasMany(HoraireBus::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(IncidentBus::class);
    }
}
