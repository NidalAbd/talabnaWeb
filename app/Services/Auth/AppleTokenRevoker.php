<?php

namespace App\Services\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sign in with Apple token management for account deletion (App Store guideline 5.1.1(v): an app
 * that supports Sign in with Apple must revoke the user's Apple tokens when the account is deleted).
 *
 * At sign-in the app forwards Apple's one-time `authorization_code`; we exchange it for a refresh
 * token and keep it (encrypted). On deletion we call Apple's /auth/revoke with it.
 *
 * Needs an Apple developer key: APPLE_TEAM_ID, APPLE_KEY_ID and APPLE_PRIVATE_KEY (the .p8 text,
 * "\n" escapes allowed) or APPLE_PRIVATE_KEY_PATH. Without them everything here is a no-op, so the
 * app keeps working and simply skips revocation until the key is configured.
 */
class AppleTokenRevoker
{
    public const TOKEN_URL = 'https://appleid.apple.com/auth/token';
    public const REVOKE_URL = 'https://appleid.apple.com/auth/revoke';
    public const AUDIENCE = 'https://appleid.apple.com';

    public function configured(): bool
    {
        return $this->clientId() !== '' && (string) config('services.apple.team_id') !== ''
            && (string) config('services.apple.key_id') !== '' && $this->privateKey() !== null;
    }

    /** At sign-in: swap the one-time authorization code for a refresh token and store it encrypted. */
    public function remember(Model $user, ?string $authorizationCode): void
    {
        if (! $authorizationCode || ! $this->configured()) {
            return;
        }
        $refreshToken = $this->exchangeCode($authorizationCode);
        if ($refreshToken) {
            $user->forceFill(['apple_refresh_token' => Crypt::encryptString($refreshToken)])->save();
        }
    }

    /** At account deletion: revoke the stored token at Apple. Never blocks the deletion itself. */
    public function forget(Model $user): void
    {
        if (empty($user->apple_refresh_token)) {
            return;
        }
        try {
            $this->revoke(Crypt::decryptString($user->apple_refresh_token));
        } catch (\Throwable $e) {
            Log::warning('apple.token.forget failed', ['message' => $e->getMessage()]);
        }
    }

    /** Exchanges the sign-in authorization code for a refresh token; null when unavailable. */
    public function exchangeCode(string $authorizationCode): ?string
    {
        if (! $this->configured()) {
            return null;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(self::TOKEN_URL, [
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'code' => $authorizationCode,
                'grant_type' => 'authorization_code',
            ]);
        } catch (\Throwable $e) {
            Log::warning('apple.token.exchange failed', ['message' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('apple.token.exchange rejected', ['status' => $response->status(), 'body' => $response->json('error')]);

            return null;
        }

        return $response->json('refresh_token');
    }

    /** Revokes a refresh token at Apple. True on success; false (logged) otherwise. */
    public function revoke(string $refreshToken): bool
    {
        if (! $this->configured()) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(self::REVOKE_URL, [
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'token' => $refreshToken,
                'token_type_hint' => 'refresh_token',
            ]);
        } catch (\Throwable $e) {
            Log::warning('apple.token.revoke failed', ['message' => $e->getMessage()]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('apple.token.revoke rejected', ['status' => $response->status()]);
        }

        return $response->successful();
    }

    /** ES256 JWT Apple accepts as the client secret. */
    public function clientSecret(?int $now = null): string
    {
        $now ??= time();
        $header = ['alg' => 'ES256', 'kid' => (string) config('services.apple.key_id'), 'typ' => 'JWT'];
        $claims = [
            'iss' => (string) config('services.apple.team_id'),
            'iat' => $now,
            'exp' => $now + 300,
            'aud' => self::AUDIENCE,
            'sub' => $this->clientId(),
        ];
        $input = self::b64url(json_encode($header)).'.'.self::b64url(json_encode($claims));

        $key = openssl_pkey_get_private((string) $this->privateKey());
        if (! $key || ! openssl_sign($input, $der, $key, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Could not sign the Apple client secret.');
        }

        return $input.'.'.self::b64url(self::derToRaw($der));
    }

    private function clientId(): string
    {
        return (string) config('services.apple.client_id');
    }

    private function privateKey(): ?string
    {
        $inline = config('services.apple.private_key');
        if ($inline) {
            return str_replace('\n', "\n", (string) $inline);
        }
        $path = config('services.apple.private_key_path');
        if ($path && is_readable($path)) {
            return (string) file_get_contents($path);
        }

        return null;
    }

    /** OpenSSL returns an ASN.1 DER signature; JWS ES256 wants the raw 64-byte R||S. */
    public static function derToRaw(string $der): string
    {
        $offset = 2;
        if (ord($der[1]) & 0x80) { // long-form sequence length
            $offset += ord($der[1]) & 0x7f;
        }
        $parts = [];
        for ($i = 0; $i < 2; $i++) {
            $len = ord($der[$offset + 1]);
            $int = substr($der, $offset + 2, $len);
            $parts[] = str_pad(ltrim($int, "\x00"), 32, "\x00", STR_PAD_LEFT);
            $offset += 2 + $len;
        }

        return $parts[0].$parts[1];
    }

    public static function rawToDer(string $raw): string
    {
        $int = function (string $b): string {
            $b = ltrim($b, "\x00");
            if ($b === '' || ord($b[0]) > 0x7f) {
                $b = "\x00".$b;
            }

            return "\x02".chr(strlen($b)).$b;
        };
        $body = $int(substr($raw, 0, 32)).$int(substr($raw, 32, 32));

        return "\x30".(strlen($body) > 127 ? "\x81".chr(strlen($body)) : chr(strlen($body))).$body;
    }

    private static function b64url(string $b): string
    {
        return rtrim(strtr(base64_encode($b), '+/', '-_'), '=');
    }
}
