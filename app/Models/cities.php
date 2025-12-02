<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class cities extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id'
    ];

    protected $casts = [
        'name' => 'array'
    ];

    /**
     * Ensure name is always returned as an array
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_array($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                    return ['ar' => $value, 'en' => $value];
                }
                return ['ar' => '', 'en' => ''];
            },
            set: function ($value) {
                if (is_array($value)) {
                    return json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                return $value;
            }
        );
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(countries::class, 'country_id');
    }

    public function getTranslatedName()
    {
        // Make sure $this->name is properly decoded from JSON to array
        $nameArray = is_array($this->name) ? $this->name : json_decode($this->name, true);

        $locale = app()->getLocale();

        // Default to English if current locale not available
        return $nameArray[$locale] ?? $nameArray['en'] ?? 'Unknown';
    }

    private function getNameArray($city) {
        if (is_string($city->name)) {
            return json_decode($city->name, true) ?: ['en' => '', 'ar' => ''];
        }
        return is_array($city->name) ? $city->name : ['en' => '', 'ar' => ''];
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photos::class, 'photoable');
    }

    public function servicePosts()
    {
        return $this->hasMany(ServicePost::class, 'city_id');
    }
}
