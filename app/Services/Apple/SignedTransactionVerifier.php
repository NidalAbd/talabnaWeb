<?php

namespace App\Services\Apple;

/**
 * Verifies a StoreKit 2 signed transaction (a JWS) WITHOUT any secret and without calling Apple.
 *
 * The iOS in_app_purchase plugin (StoreKit 2, the default) hands the app the transaction as a JWS. It is signed with ES256 by a
 * leaf certificate that Apple issues, and the header carries the certificate chain (x5c: leaf, intermediate, root). We trust ONLY
 * our own copy of "Apple Root CA - G3": the chain must lead to it, each certificate must be valid when the transaction was signed,
 * and the signature must verify with the leaf's key. Whatever the payload says is then trustworthy (bundle id, product,
 * transaction id, environment, expiry, refund).
 *
 * Needs only the PHP openssl extension.
 */
class SignedTransactionVerifier
{
    /** Apple Root CA - G3 (public certificate from https://www.apple.com/certificateauthority/), SHA-256 63343abf...3e9179. */
    public const APPLE_ROOT_CA_G3 = <<<'PEM'
-----BEGIN CERTIFICATE-----
MIICQzCCAcmgAwIBAgIILcX8iNLFS5UwCgYIKoZIzj0EAwMwZzEbMBkGA1UEAwwS
QXBwbGUgUm9vdCBDQSAtIEczMSYwJAYDVQQLDB1BcHBsZSBDZXJ0aWZpY2F0aW9u
IEF1dGhvcml0eTETMBEGA1UECgwKQXBwbGUgSW5jLjELMAkGA1UEBhMCVVMwHhcN
MTQwNDMwMTgxOTA2WhcNMzkwNDMwMTgxOTA2WjBnMRswGQYDVQQDDBJBcHBsZSBS
b290IENBIC0gRzMxJjAkBgNVBAsMHUFwcGxlIENlcnRpZmljYXRpb24gQXV0aG9y
aXR5MRMwEQYDVQQKDApBcHBsZSBJbmMuMQswCQYDVQQGEwJVUzB2MBAGByqGSM49
AgEGBSuBBAAiA2IABJjpLz1AcqTtkyJygRMc3RCV8cWjTnHcFBbZDuWmBSp3ZHtf
TjjTuxxEtX/1H7YyYl3J6YRbTzBPEVoA/VhYDKX1DyxNB0cTddqXl5dvMVztK517
IDvYuVTZXpmkOlEKMaNCMEAwHQYDVR0OBBYEFLuw3qFYM4iapIqZ3r6966/ayySr
MA8GA1UdEwEB/wQFMAMBAf8wDgYDVR0PAQH/BAQDAgEGMAoGCCqGSM49BAMDA2gA
MGUCMQCD6cHEFl4aXTQY2e3v9GwOAEZLuN+yRhHFD/3meoyhpmvOwgPUnPWTxnS4
at+qIxUCMG1mihDK1A3UT82NQz60imOlM27jbdoXt2QfyFMm+YhidDkLF1vLUagM
6BgD56KyKA==
-----END CERTIFICATE-----
PEM;

    private const OID_LEAF = '1.2.840.113635.100.6.11.1';         // Mac App Store receipt signing
    private const OID_INTERMEDIATE = '1.2.840.113635.100.6.2.1';   // WWDR intermediate

    private string $rootPem;

    /** @param string|null $rootPem only tests pass their own root; the app always trusts Apple's */
    public function __construct(?string $rootPem = null)
    {
        $this->rootPem = $rootPem ?? self::APPLE_ROOT_CA_G3;
    }

    /** A JWS is three base64url parts separated by dots; a legacy App Store receipt is one base64 blob. */
    public static function looksLikeJws(string $value): bool
    {
        return preg_match('/^eyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]*$/', $value) === 1;
    }

    /** @return array{ok:bool, payload?:array<string,mixed>, error?:string} */
    public function verify(string $jws): array
    {
        $parts = explode('.', $jws);
        if (count($parts) !== 3) {
            return $this->fail('Not a signed transaction');
        }
        [$h64, $p64, $s64] = $parts;

        $header = json_decode((string) $this->b64urlDecode($h64), true);
        $payload = json_decode((string) $this->b64urlDecode($p64), true);
        $sig = $this->b64urlDecode($s64);
        if (! is_array($header) || ! is_array($payload) || $sig === null || $sig === '') {
            return $this->fail('Malformed signed transaction');
        }
        // Only ES256 is accepted: never "none", never an HMAC keyed with a public value.
        if (($header['alg'] ?? null) !== 'ES256' || ! isset($header['x5c']) || ! is_array($header['x5c']) || count($header['x5c']) < 2) {
            return $this->fail('Unsupported signature algorithm');
        }

        $pem = [];
        foreach (array_slice($header['x5c'], 0, 3) as $der) {
            if (! is_string($der) || base64_decode($der, true) === false) {
                return $this->fail('Malformed certificate chain');
            }
            $pem[] = "-----BEGIN CERTIFICATE-----\n".chunk_split($der, 64, "\n")."-----END CERTIFICATE-----\n";
        }
        [$leaf, $intermediate] = [$pem[0], $pem[1]];

        // The trust anchor is OUR root. A root sent inside the token is ignored (anyone could send their own).
        $rootKey = openssl_pkey_get_public($this->rootPem);
        $interKey = openssl_pkey_get_public($intermediate);
        $leafKey = openssl_pkey_get_public($leaf);
        if (! $rootKey || ! $interKey || ! $leafKey) {
            return $this->fail('Unreadable certificate');
        }
        if (openssl_x509_verify($intermediate, $rootKey) !== 1 || openssl_x509_verify($leaf, $interKey) !== 1) {
            return $this->fail('Certificate chain is not issued by Apple');
        }

        // Valid when the transaction was signed (Apple's leaf certificates expire long before old transactions do).
        $at = isset($payload['signedDate']) ? (int) floor(((float) $payload['signedDate']) / 1000) : time();
        foreach ([$leaf, $intermediate, $this->rootPem] as $cert) {
            $info = openssl_x509_parse($cert);
            if (! $info || $at < ($info['validFrom_time_t'] ?? PHP_INT_MAX) || $at > ($info['validTo_time_t'] ?? 0)) {
                return $this->fail('Certificate was not valid when the transaction was signed');
            }
        }
        if (! $this->hasExtension($leaf, self::OID_LEAF) || ! $this->hasExtension($intermediate, self::OID_INTERMEDIATE)) {
            return $this->fail('Certificate is not an App Store signing certificate');
        }

        $der = $this->rawSignatureToDer($sig);
        if ($der === null || openssl_verify($h64.'.'.$p64, $der, $leafKey, OPENSSL_ALGO_SHA256) !== 1) {
            return $this->fail('Signature does not match');
        }

        return ['ok' => true, 'payload' => $payload];
    }

    /**
     * The business checks on a verified transaction. Returns an error message, or null when it is what the app claims.
     *
     * @param array<string,mixed> $t decoded payload
     */
    public function checkTransaction(array $t, string $bundleId, string $productId, ?string $transactionId = null, bool $subscription = false): ?string
    {
        if (($t['bundleId'] ?? null) !== $bundleId) {
            return 'Purchase is not for this app';
        }
        if (($t['productId'] ?? null) !== $productId) {
            return 'Purchase is for a different product';
        }
        if ($transactionId !== null && $transactionId !== '' && (string) ($t['transactionId'] ?? '') !== $transactionId) {
            return 'Transaction does not match';
        }
        if (! empty($t['revocationDate'])) {
            return 'Purchase was refunded';
        }
        if ($subscription) {
            if (empty($t['expiresDate'])) {
                return 'Subscription has no expiry';
            }
            if (((float) $t['expiresDate']) / 1000 < time()) {
                return 'Subscription has expired';
            }
        }

        return null;
    }

    private function fail(string $error): array
    {
        return ['ok' => false, 'error' => $error];
    }

    private function hasExtension(string $certPem, string $oid): bool
    {
        $info = openssl_x509_parse($certPem);
        $ext = $info['extensions'] ?? [];

        return array_key_exists($oid, $ext) || str_contains(json_encode(array_keys($ext)), $oid);
    }

    private function b64urlDecode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        return $decoded === false ? null : $decoded;
    }

    /** ES256 signatures in a JWS are the raw 64 bytes R||S; openssl wants DER. */
    private function rawSignatureToDer(string $raw): ?string
    {
        if (strlen($raw) !== 64) {
            return null;
        }
        $int = function (string $b): string {
            $b = ltrim($b, "\x00");
            if ($b === '' || ord($b[0]) > 0x7F) {
                $b = "\x00".$b;
            }

            return "\x02".chr(strlen($b)).$b;
        };
        $body = $int(substr($raw, 0, 32)).$int(substr($raw, 32));

        return "\x30".chr(strlen($body)).$body;
    }
}
