<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A seller's storefront (2026-10-08). One per user; shown while the owner has Business or a shop unlock. */
class Shop extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'week_hours' => 'array',
        'featured_post_ids' => 'array',
        'offer_until' => 'date',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The public path of the shop page (no locale prefix). */
    public function path(): string
    {
        return '/shop/' . ($this->slug ?: $this->user_id);
    }

    /** The offer, while it runs. */
    public function currentOffer(): ?string
    {
        if (! $this->offer) {
            return null;
        }

        return $this->offer_until === null || $this->offer_until->endOfDay()->isFuture() ? $this->offer : null;
    }
}
