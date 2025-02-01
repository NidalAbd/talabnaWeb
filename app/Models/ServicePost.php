<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServicePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'sub_category',
        'price',
        'price_currency',
        'location_latitudes',
        'location_longitudes',
        'type',
        'have_badge',
        'badge_duration',
        'view_num',
        'state'
    ];

    public static function distance($lat1, $lng1, $lat2, $lng2): float|int
    {
        $earth_radius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));

        return $earth_radius * $c;
    }

    public function distanceExpression($userLatitude, $userLongitude): string
    {
        return "ROUND((6371 * acos(cos(radians($userLatitude))
              * cos(radians(location_latitudes))
              * cos(radians(location_longitudes)
              - radians($userLongitude))
              + sin(radians($userLatitude))
              * sin(radians(location_latitudes)))), 3)";
    }




    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photos::class, 'photoable');
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


    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(categories::class,'categories_id');
    }

    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(Sub_categories::class, 'user_subcategory', 'user_id', 'sub_categories_id');
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(countries::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(cities::class);
    }
}
