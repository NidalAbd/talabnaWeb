<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One paid AI action. See the create_ai_requests_table migration for the life cycle. */
class AiRequest extends Model
{
    public const PROCESSING = 'processing';
    public const SUCCEEDED = 'succeeded';
    public const FAILED = 'failed';               // the AI failed; the points were returned
    public const REFUND_FAILED = 'refund_failed'; // the AI failed and returning the points failed: needs an admin

    protected $guarded = [];
    protected $casts = ['result' => 'array', 'completed_at' => 'datetime', 'refunded_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFinished(): bool
    {
        return $this->status !== self::PROCESSING;
    }

    public function kind(): string
    {
        return match ($this->feature) {
            'generate_video' => 'video',
            'generate_image' => 'image',
            default => 'text',
        };
    }
}
