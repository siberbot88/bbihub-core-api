<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'room_id',
        'user_id',
        'user_type',
        'message',
        'attachment_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who sent this message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter messages in a specific room
     */
    public function scopeInRoom($query, string $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope to get only unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get messages after a specific timestamp
     */
    public function scopeAfter($query, $timestamp)
    {
        return $query->where('created_at', '>', $timestamp);
    }

    /**
     * Mark this message as read
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    /**
     * Mark multiple messages as read
     */
    public static function markMultipleAsRead(array $messageIds): int
    {
        return self::whereIn('id', $messageIds)->update(['is_read' => true]);
    }
}
