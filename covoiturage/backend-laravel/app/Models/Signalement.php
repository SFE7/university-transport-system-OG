<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signalement extends Model
{
    use HasFactory;

    protected $table = 'signalements';

    protected $fillable = [
        'reporter_id',
        'reported_id',
        'conducteur_id',
        'trajet_id',
        'reason',
        'description',
        'status',
    ];

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'reporter_id');
    }

    public function conducteur(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'conducteur_id');
    }

    public function trajet(): BelongsTo
    {
        return $this->belongsTo(Trajet::class, 'trajet_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->membre();
    }

    public function reported(): BelongsTo
    {
        return $this->conducteur();
    }
}
