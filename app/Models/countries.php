<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class countries extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_code',
        'currency_code',
        'currency_name'
    ];

    protected $casts = [
        'name' => 'array',
        'currency_name' => 'array'
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(cities::class, 'country_id');
    }

    public function servicePosts(): HasMany
    {
        return $this->hasMany(ServicePost::class, 'country_id');
    }

    public function getTranslatedName($nameArray = null)
    {
        if ($nameArray === null) {
            $nameArray = $this->name;
        }

        $locale = app()->getLocale();
        return $nameArray[$locale] ?? $nameArray['en'] ?? 'Unknown';
    }

    public function getTranslatedCurrencyName()
    {
        $locale = app()->getLocale();
        return $this->currency_name[$locale] ?? $this->currency_name['en'] ?? 'Unknown';
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photos::class, 'photoable');
    }
}
