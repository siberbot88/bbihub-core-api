<?php

namespace App\Models;

use Database\Factories\WorkshopDocumentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopDocument extends Model
{
    /** @use HasFactory<WorkshopDocumentFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    /**
     * Relasi ke Workshop
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class, 'workshop_uuid');
    }
}
