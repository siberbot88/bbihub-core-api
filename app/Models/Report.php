<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<ReportFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'workshop_uuid',
        'report_type',
        'report_data',
        'photo',
    ];

    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid', 'uuid');
    }
}
