<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
