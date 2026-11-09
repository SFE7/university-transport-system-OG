<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avis extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'conducteur_id',
        'trajet_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'reviewer_id');
    }

    public function conducteur(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'conducteur_id');
    }

    public function trajet(): BelongsTo
    {
        return $this->belongsTo(Trajet::class);
    }
}
