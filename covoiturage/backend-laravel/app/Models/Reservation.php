<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'membre_id',
        'trajet_id',
        'status',
    ];

    public function passager(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'membre_id');
    }

    public function trajet(): BelongsTo
    {
        return $this->belongsTo(Trajet::class);
    }
}
