<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSoumis extends Model
{
    use HasFactory;

    protected $table = 'documents_soumis';

    protected $fillable = [
        'membre_id',
        'type',
        'file_path',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'membre_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'reviewed_by');
    }
}
