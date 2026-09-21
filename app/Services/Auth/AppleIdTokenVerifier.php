<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Verifies a "Sign in with Apple" identity token (an RS256 JWT).
 *
 * Uses ext-openssl only (no JWT library): signature against Apple's published keys (JWKS,
 * cached), issuer, audience (the app's bundle id), expiry and — when supplied — the nonce.
 */
class AppleIdTokenVerifier
{
    public const ISSUER = 'https://appleid.apple.com';
    public const JWKS_URL = 'https://appleid.apple.com/auth/keys';
    private const JWKS_CACHE_KEY = 'apple.jwks';

    /** @return array<string,mixed>|null verified claims, or null if invalid */
    public function verify(string $jwt, ?string $rawNonce = null): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }
        [$h64, $p64, $s64] = $parts;

        $header = json_decode(self::b64urlDecode($h64), true);
        $claims = json_decode(self::b64urlDecode($p64), true);
        $signature = self::b64urlDecode($s64);
        if (! is_array($header) || ! is_array($claims) || $signature === '') {
            return null;
        }

        // Only RS256 — rejects `alg: none` and HMAC key-confusion tokens.
        if (($header['alg'] ?? null) !== 'RS256' || empty($header['kid'])) {
            return null;
        }

        $jwk = $this->findKey((string) $header['kid']);
        $pem = $jwk ? self::jwkToPem($jwk) : null;
        if (! $pem || openssl_verify("$h64.$p64", $signature, $pem, OPENSSL_ALGO_SHA256) !== 1) {
            return null;
        }

        if (($claims['iss'] ?? null) !== self::ISSUER) {
            return null;
        }

        $allowed = array_filter([config('services.apple.client_id'), config('services.apple.services_id')]);
        if (! $allowed || ! in_array($claims['aud'] ?? null, $allowed, true)) {
            return null;
        }
        if (! isset($claims['exp']) || (int) $claims['exp'] < time() || empty($claims['sub'])) {
            return null;
        }

        // The app sends Apple sha256(rawNonce); Apple echoes that hash in the token.
        if ($rawNonce !== null && ! hash_equals(hash('sha256', $rawNonce), (string) ($claims['nonce'] ?? ''))) {
            return null;
        }

        return $claims;
    }

    /** Apple sends email_verified as bool or the string "true". */
    public static function emailVerified(array $claims): bool
    {
        $v = $claims['email_verified'] ?? false;

        return $v === true || $v === 'true';
    }

    private function findKey(string $kid): ?array
    {
        foreach ([false, true] as $forceRefresh) {
            foreach ($this->jwks($forceRefresh) as $key) {
                if (($key['kid'] ?? null) === $kid && ($key['kty'] ?? null) === 'RSA') {
                    return $key;
                }
            }
        }

        return null; // unknown kid even after a refresh
    }

    /** @return array<int,array<string,mixed>> */
    private function jwks(bool $forceRefresh): array
    {
        if ($forceRefresh) {
            Cache::forget(self::JWKS_CACHE_KEY);
        }

        return Cache::remember(self::JWKS_CACHE_KEY, 21600, function () {
            try {
                $resp = Http::timeout(5)->get(self::JWKS_URL);
            } catch (\Throwable $e) {
                return [];
            }

            return $resp->ok() ? (array) ($resp->json('keys') ?? []) : [];
        });
    }

    private static function jwkToPem(array $jwk): ?string
    {
        $n = self::b64urlDecode($jwk['n'] ?? '');
        $e = self::b64urlDecode($jwk['e'] ?? '');
        if ($n === '' || $e === '') {
            return null;
        }
        $rsa = self::derSequence(self::derInteger($n).self::derInteger($e));
        $alg = hex2bin('300d06092a864886f70d0101010500'); // rsaEncryption + NULL
        $bit = "\x03".self::derLength(strlen($rsa) + 1)."\x00".$rsa;
        $spki = self::derSequence($alg.$bit);

        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($spki), 64, "\n")."-----END PUBLIC KEY-----\n";
    }

    private static function derLength(int $len): string
    {
        if ($len < 0x80) {
            return chr($len);
        }
        $bytes = ltrim(pack('N', $len), "\x00");

        return chr(0x80 | strlen($bytes)).$bytes;
    }

    private static function derInteger(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '' || ord($bytes[0]) > 0x7f) {
            $bytes = "\x00".$bytes;
        }

        return "\x02".self::derLength(strlen($bytes)).$bytes;
    }

    private static function derSequence(string $content): string
    {
        return "\x30".self::derLength(strlen($content)).$content;
    }

    private static function b64urlDecode(string $s): string
    {
        $s = strtr($s, '-_', '+/');
        if ($pad = strlen($s) % 4) {
            $s .= str_repeat('=', 4 - $pad);
        }
        $out = base64_decode($s, true);

        return $out === false ? '' : $out;
    }
}
