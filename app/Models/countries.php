<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class countries extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_code',
        'currency_code',
        'currency_name',
        'flag'
    ];

    protected $casts = [
        'name' => 'array',
        'currency_name' => 'array'
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

    /**
     * Ensure currency_name is always returned as an array
     */
    protected function currencyName(): Attribute
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

    public function cities(): HasMany
    {
        return $this->hasMany(cities::class, 'country_id');
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
