<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'workshop_uuid',
        'report_type',
        'report_data',
        'photo',
    ];

    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid', 'uuid');
    }
}
