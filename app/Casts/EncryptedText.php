<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

/**
 * Encryption at rest for chat text (2026-10-03): stored AES-256 encrypted
 * with the app key. Rows written before encryption are read as plain text.
 */
class EncryptedText implements CastsAttributes
{
    private const PREFIX = 'enc:';

    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null || !str_starts_with((string) $value, self::PREFIX)) return $value;
        try {
            return Crypt::decryptString(substr($value, strlen(self::PREFIX)));
        } catch (\Throwable) {
            return '';
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') return $value;
        return self::PREFIX . Crypt::encryptString((string) $value);
    }
}
