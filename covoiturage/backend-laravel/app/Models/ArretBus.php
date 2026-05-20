<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArretBus extends Model
{
    use HasFactory;

    protected $table = 'arrets_bus';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'order',
        'ligne_bus_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(LigneBus::class);
    }
}
