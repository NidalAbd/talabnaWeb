<?php

namespace Tests\Support;

/**
 * Builds an Apple-shaped certificate chain (root -> WWDR-style intermediate -> leaf, with Apple's marker extensions) and signs
 * StoreKit 2 style transactions with it, so the verifier can be tested end to end without any real Apple data.
 */
class AppleJwsFactory
{
    public string $rootPem;
    private $leafKey;
    private string $leafPem;
    private string $interPem;

    public function __construct(?string $foreignRootPem = null)
    {
        $conf = tempnam(sys_get_temp_dir(), 'ossl');
        file_put_contents($conf, <<<'CONF'
[req]
distinguished_name = dn
[dn]
[v3_root]
basicConstraints = critical,CA:TRUE
keyUsage = critical,keyCertSign,cRLSign
[v3_inter]
basicConstraints = critical,CA:TRUE
keyUsage = critical,keyCertSign,cRLSign
1.2.840.113635.100.6.2.1 = ASN1:NULL
[v3_leaf]
basicConstraints = critical,CA:FALSE
keyUsage = critical,digitalSignature
1.2.840.113635.100.6.11.1 = ASN1:NULL
CONF);
        $mk = fn () => openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC, 'private_key_bits' => 2048]);
        $req = fn ($key, string $cn) => openssl_csr_new(['commonName' => $cn], $key, ['digest_alg' => 'sha256', 'config' => $conf]);
        $sign = fn ($csr, $caCert, $caKey, string $ext, int $serial) => openssl_csr_sign($csr, $caCert, $caKey, 365, ['digest_alg' => 'sha256', 'config' => $conf, 'x509_extensions' => $ext], $serial);
        $pem = function ($cert): string {
            openssl_x509_export($cert, $out);

            return $out;
        };

        $rootKey = $mk();
        $rootCert = $sign($req($rootKey, 'Test Apple Root'), null, $rootKey, 'v3_root', 1);
        $interKey = $mk();
        $interCert = $sign($req($interKey, 'Test WWDR'), $rootCert, $rootKey, 'v3_inter', 2);
        $this->leafKey = $mk();
        $leafCert = $sign($req($this->leafKey, 'Test Leaf'), $interCert, $interKey, 'v3_leaf', 3);

        $this->rootPem = $pem($rootCert);
        $this->interPem = $pem($interCert);
        $this->leafPem = $pem($leafCert);
        unlink($conf);
    }

    /** @param array<string,mixed> $claims */
    public function transaction(array $claims = [], array $header = [], ?string $signWith = null): string
    {
        $payload = array_merge([
            'transactionId' => '2000000111111111',
            'originalTransactionId' => '2000000111111111',
            'bundleId' => 'com.example.app',
            'productId' => 'points_10',
            'type' => 'Consumable',
            'purchaseDate' => (int) (microtime(true) * 1000),
            'signedDate' => (int) (microtime(true) * 1000),
            'environment' => 'Sandbox',
        ], $claims);
        $header = array_merge(['alg' => 'ES256', 'typ' => 'JWT', 'x5c' => [$this->der($this->leafPem), $this->der($this->interPem), $this->der($this->rootPem)]], $header);

        $signingInput = $this->b64(json_encode($header)).'.'.$this->b64(json_encode($payload));
        openssl_sign($signingInput, $der, $signWith ? openssl_pkey_get_private($signWith) : $this->leafKey, OPENSSL_ALGO_SHA256);

        return $signingInput.'.'.$this->b64($this->derToRaw($der));
    }

    private function der(string $pem): string
    {
        return preg_replace('/\s+|-----[A-Z ]+-----/', '', $pem);
    }

    private function b64(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    /** DER ECDSA signature -> raw R||S (what a JWS carries). */
    private function derToRaw(string $der): string
    {
        $i = 2 + (ord($der[1]) & 0x80 ? ord($der[1]) & 0x7F : 0);
        $raw = '';
        for ($k = 0; $k < 2; $k++) {
            $len = ord($der[$i + 1]);
            $raw .= str_pad(ltrim(substr($der, $i + 2, $len), "\x00"), 32, "\x00", STR_PAD_LEFT);
            $i += 2 + $len;
        }

        return $raw;
    }
}
