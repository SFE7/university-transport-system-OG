<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentBus extends Model
{
    use HasFactory;

    protected $table = 'incidents_bus';

    protected $fillable = [
        'ligne_bus_id',
        'reported_by',
        'type',
        'description',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(LigneBus::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'reported_by');
    }
}
