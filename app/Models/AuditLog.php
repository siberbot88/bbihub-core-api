<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_email',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user that caused the audit log
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the auditable model
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Create audit log entry
     */
    public static function log(
        string $event,
        ?User $user = null,
        $auditable = null,
        array $oldValues = [],
        array $newValues = []
    ) {
        try {
            $data = [
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'event' => $event,
                'auditable_type' => $auditable ? get_class($auditable) : null,
                'auditable_id' => $auditable?->id ?? $auditable?->getKey(),
                'old_values' => empty($oldValues) ? null : $oldValues,
                'new_values' => empty($newValues) ? null : $newValues,
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent() ?? 'Unknown',
            ];

            // Create and immediately save
            $log = new static($data);
            $log->save();

            return $log;
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('AuditLog creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? []
            ]);

            // Don't break the app
            return null;
        }
    }
}
