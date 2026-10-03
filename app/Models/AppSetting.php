<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/** Admin-editable key/value settings, cached for 10 minutes. */
class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        try {
            $all = Cache::remember('app_settings.all', 600, fn () => Schema::hasTable('app_settings')
                ? self::pluck('value', 'key')->all() : []);
        } catch (\Throwable) {
            $all = [];
        }
        return array_key_exists($key, $all) && $all[$key] !== null ? $all[$key] : $default;
    }

    public static function put(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget('app_settings.all');
    }
}
