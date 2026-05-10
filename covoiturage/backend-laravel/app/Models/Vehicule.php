<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicule extends Model
{
    use HasFactory;

    protected $table = 'vehicules';

    protected $fillable = [
        'conducteur_id',
        'marque',
        'modele',
        'immatriculation',
        'couleur',
    ];

    public function conducteur(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'conducteur_id');
    }
}
