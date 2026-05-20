<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusPosition extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'chauffeur_id',
        'latitude',
        'longitude',
        'is_sharing',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_sharing' => 'boolean',
    ];

    protected $dates = [
        'updated_at',
    ];

    public function chauffeur(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'chauffeur_id');
    }
}
