<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

/** A top-up pack: extra uses of a plan allowance, valid until the current period ends. */
class SubscriptionAddon extends Model
{
    use HasTranslations;

    /** Plan features a pack can add to (numeric allowances only). */
    public const FEATURES = ['ai_images_per_month', 'featured_posts'];

    protected $translatable = ['name'];

    protected $fillable = ['slug', 'name', 'feature_key', 'amount', 'price_points', 'is_active', 'sort_order'];

    protected $casts = [
        'name' => 'array',
        'amount' => 'integer',
        'price_points' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
