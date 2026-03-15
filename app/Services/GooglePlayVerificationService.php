<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GooglePlayVerificationService
{
    // Product ID -> points mapping
    public const PRODUCT_POINTS = [
        'points-1' => 1,
        'points-3' => 3,
        'points-5' => 5,
        'points-10' => 10,
        'points-25' => 25,
        'points-50' => 50,
        'points-100' => 100,
    ];

    /**
     * Verify a Google Play purchase using the Android Publisher API
     */
    public function verifyPurchase(string $productId, string $purchaseToken): array
    {
        $packageName = config('services.google_play.package_name');

        if (empty($packageName)) {
            Log::error('Google Play package name not configured');
            return ['verified' => false, 'error' => 'Google Play not configured'];
        }

        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return ['verified' => false, 'error' => 'Failed to get access token'];
            }

            $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$purchaseToken}";

            $response = Http::withToken($accessToken)->get($url);

            if (!$response->successful()) {
                Log::error('Google Play verification failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'product_id' => $productId,
                ]);
                return ['verified' => false, 'error' => 'Verification request failed'];
            }

            $data = $response->json();

            // purchaseState: 0 = Purchased, 1 = Canceled, 2 = Pending
            if (($data['purchaseState'] ?? -1) !== 0) {
                return [
                    'verified' => false,
                    'error' => 'Purchase not in valid state',
                    'state' => $data['purchaseState'] ?? 'unknown',
                ];
            }

            // consumptionState: 0 = Not consumed, 1 = Consumed
            // acknowledgementState: 0 = Not acknowledged, 1 = Acknowledged
            return [
                'verified' => true,
                'order_id' => $data['orderId'] ?? null,
                'purchase_time' => $data['purchaseTimeMillis'] ?? null,
                'consumption_state' => $data['consumptionState'] ?? 0,
                'acknowledgement_state' => $data['acknowledgementState'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Google Play verification exception: ' . $e->getMessage());
            return ['verified' => false, 'error' => 'Verification exception: ' . $e->getMessage()];
        }
    }

    /**
     * Acknowledge/consume a purchase after crediting points
     */
    public function acknowledgePurchase(string $productId, string $purchaseToken): bool
    {
        $packageName = config('services.google_play.package_name');

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) return false;

            $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$purchaseToken}:acknowledge";

            $response = Http::withToken($accessToken)->post($url);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to acknowledge purchase: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get points amount for a product ID
     */
    public function getPointsForProduct(string $productId): ?int
    {
        return self::PRODUCT_POINTS[$productId] ?? null;
    }

    /**
     * Get Google API access token using service account credentials
     */
    private function getAccessToken(): ?string
    {
        return Cache::remember('google_play_access_token', 3500, function () {
            $credentialsPath = config('services.google_play.credentials_path');

            if (empty($credentialsPath) || !file_exists($credentialsPath)) {
                Log::error('Google Play service account credentials not found: ' . ($credentialsPath ?? 'null'));
                return null;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            if (!$credentials) {
                Log::error('Failed to parse Google Play credentials');
                return null;
            }

            // Create JWT
            $header = base64url_encode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]));

            $now = time();
            $claims = base64url_encode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/androidpublisher',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]));

            $signature = '';
            $privateKey = openssl_pkey_get_private($credentials['private_key']);
            openssl_sign("$header.$claims", $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $signature = base64url_encode($signature);

            $jwt = "$header.$claims.$signature";

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Failed to get Google access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        });
    }
}

/**
 * URL-safe base64 encode
 */
if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
