<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class ServicePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'price_currency_code',
        'price_currency_name',
        'location_latitudes',
        'location_longitudes',
        'type',
        'have_badge',
        'badge_type_id',
        'badge_duration',
        'badge_expires_at',
        'view_count',
        'favorites_count',
        'report_count',
        'state',
        'categories_id',
        'sub_categories_id',
        'country_id',
        'city_id',
    ];

    protected $casts = [
        'price_currency_name' => 'array', // This will automatically handle JSON encoding/decoding
        'badge_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['currency_name'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new service post with a badge
        static::creating(function ($servicePost) {
            // Set badge expiration date if badge is not 'عادي' and duration is set
            if ($servicePost->have_badge !== 'عادي' && $servicePost->badge_duration > 0) {
                $servicePost->badge_expires_at = Carbon::now()->addDays($servicePost->badge_duration);
            }
        });

        // When updating a service post
        static::updating(function ($servicePost) {
            // If badge or duration has changed
            if ($servicePost->isDirty('have_badge') || $servicePost->isDirty('badge_duration')) {
                if ($servicePost->have_badge !== 'عادي' && $servicePost->badge_duration > 0) {
                    $servicePost->badge_expires_at = Carbon::now()->addDays($servicePost->badge_duration);
                } else if ($servicePost->have_badge === 'عادي') {
                    $servicePost->badge_expires_at = null;
                    $servicePost->badge_duration = 0;
                }
            }
        });
    }

    /**
     * Check if the badge has expired and update if necessary.
     *
     * @return bool True if the badge was updated, false otherwise
     */
    public function checkBadgeExpiration()
    {
        // Method 1: Check using badge_expires_at (preferred method)
        if ($this->have_badge !== 'عادي' && $this->badge_expires_at && $this->badge_expires_at->isPast()) {
            $this->have_badge = 'عادي';
            $this->badge_duration = 0;
            $this->badge_expires_at = null;
            $this->save();
            return true;
        }

        // Method 2: Fallback to calculating based on creation date + duration
        // This ensures backwards compatibility with existing records
        if ($this->have_badge !== 'عادي' && $this->badge_duration > 0 && !$this->badge_expires_at) {
            $expirationDate = Carbon::parse($this->created_at)->addDays($this->badge_duration);

            if (Carbon::now()->greaterThanOrEqualTo($expirationDate)) {
                $this->have_badge = 'عادي';
                $this->badge_duration = 0;
                $this->badge_expires_at = null;
                $this->save();
                return true;
            } else {
                // Update the badge_expires_at field for future checks
                $this->badge_expires_at = $expirationDate;
                $this->save();
            }
        }

        return false;
    }

    /**
     * Calculate remaining days for badge
     *
     * @return int|null Number of days remaining or null if no badge
     */
    public function getBadgeRemainingDays()
    {
        if ($this->have_badge === 'عادي' || !$this->badge_duration) {
            return null;
        }

        if ($this->badge_expires_at) {
            $now = Carbon::now();
            if ($this->badge_expires_at->gt($now)) {
                return $now->diffInDays($this->badge_expires_at);
            }
            return 0;
        }

        // Fallback calculation
        $expirationDate = Carbon::parse($this->created_at)->addDays($this->badge_duration);
        $now = Carbon::now();

        if ($expirationDate->gt($now)) {
            return $now->diffInDays($expirationDate);
        }

        return 0;
    }

    /**
     * Update badge with new type and duration
     *
     * @param string $badgeType The new badge type
     * @param int $duration The new badge duration in days
     * @return bool Success status
     */
    public function updateBadge(string $badgeType, int $duration)
    {
        $this->have_badge = $badgeType;
        $this->badge_duration = $duration;

        if ($badgeType !== 'عادي' && $duration > 0) {
            $this->badge_expires_at = Carbon::now()->addDays($duration);
        } else {
            $this->badge_expires_at = null;
        }

        return $this->save();
    }

    /**
     * Get currency name based on current locale
     */
    public function getCurrencyNameAttribute()
    {
        $locale = app()->getLocale();

        if (is_array($this->price_currency_name) && isset($this->price_currency_name[$locale])) {
            return $this->price_currency_name[$locale];
        }

        // Fallback
        return $locale == 'ar' ? 'دولار امريكي' : 'US Dollar';
    }

    /**
     * Calculate distance between two geographic coordinates
     */
    public static function distance($lat1, $lng1, $lat2, $lng2): float|int
    {
        $earth_radius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));

        return $earth_radius * $c;
    }

    /**
     * Get SQL expression for distance calculation
     */
    public function distanceExpression($userLatitude, $userLongitude): string
    {
        return "ROUND((6371 * acos(cos(radians($userLatitude))
              * cos(radians(location_latitudes))
              * cos(radians(location_longitudes)
              - radians($userLongitude))
              + sin(radians($userLatitude))
              * sin(radians(location_latitudes)))), 3)";
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get currency code
     */
    public function getCurrencyCode()
    {
        return $this->country->currency_code ?? 'USD';
    }

    /* Relationships */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photos::class, 'photoable');
    }

    public function subCategory()
    {
        return $this->belongsTo(Sub_categories::class, 'sub_categories_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favoritesCount()
    {
        return $this->favorites()->count();
    }

    public function isFavoritedBy(User $user)
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class,'categories_id');
    }

    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(Sub_categories::class, 'user_subcategory', 'user_id', 'sub_categories_id');
    }

    public function country()
    {
        return $this->belongsTo(countries::class, 'country_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(cities::class);
    }

    public function badgeType(): BelongsTo
    {
        return $this->belongsTo(BadgeType::class, 'badge_type_id');
    }

    /**
     * Get badge info (from relationship or fallback to have_badge)
     */
    public function getBadgeInfo(): array
    {
        if ($this->badgeType) {
            return [
                'id' => $this->badgeType->id,
                'name' => $this->badgeType->name,
                'name_ar' => $this->badgeType->name_ar,
                'name_en' => $this->badgeType->name_en,
                'slug' => $this->badgeType->slug,
                'color' => $this->badgeType->color,
                'icon' => $this->badgeType->icon,
                'is_default' => $this->badgeType->is_default,
            ];
        }

        // Fallback for old data
        return [
            'id' => null,
            'name' => ['ar' => $this->have_badge, 'en' => $this->have_badge],
            'name_ar' => $this->have_badge,
            'name_en' => $this->have_badge,
            'slug' => null,
            'color' => $this->have_badge === 'ماسي' ? '#9C27B0' : ($this->have_badge === 'ذهبي' ? '#FFD700' : '#808080'),
            'icon' => $this->have_badge === 'ماسي' ? 'mdi-diamond-stone' : ($this->have_badge === 'ذهبي' ? 'mdi-star' : 'mdi-tag'),
            'is_default' => $this->have_badge === 'عادي',
        ];
    }
}
