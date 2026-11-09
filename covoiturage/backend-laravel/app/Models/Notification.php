<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'membre_id',
        'message',
        'type',
        'target_role',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class);
    }
}
