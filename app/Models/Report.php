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
        'workshop_uuid',
        'report_type',
        'report_data',
        'photo',
        'status'
    ];

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class, 'workshop_uuid', 'id');
    }

    /**
     * Accessor to get the owner (user) through workshop
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'workshop_uuid', 'id')
            ->join('workshops', 'workshops.user_uuid', '=', 'users.id')
            ->where('workshops.id', $this->workshop_uuid);
    }
}
