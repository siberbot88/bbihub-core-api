<?php

namespace App\Models;

use Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    /** @use HasFactory<FeedbackFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'transaction_uuid',
        'rating',
        'comment',
        'submitted_at',
    ];

    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class, 'transaction_uuid');
    }
}
