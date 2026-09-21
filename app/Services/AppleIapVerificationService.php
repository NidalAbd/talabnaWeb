<?php

namespace App\Services;

use App\Services\Apple\SignedTransactionVerifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies a consumable App Store purchase (points packs) — the Apple counterpart of
 * GooglePlayVerificationService. The iOS app posts the App Store receipt; we send it to Apple's
 * verifyReceipt (production first, sandbox when Apple answers 21007, which is what TestFlight and
 * App Review produce) and only trust what Apple returns: bundle id, product and transaction id.
 *
 * Needs APPLE_IAP_SHARED_SECRET; without it verification fails closed (never credits).
 * verifyReceipt is Apple's legacy endpoint (still working); the App Store Server API replacement
 * needs a .p8 key from the developer account, and this class is the one place to swap it in.
 */
class AppleIapVerificationService
{
    public function __construct(private SignedTransactionVerifier $jws)
    {
    }

    public const PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';
    public const SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

    /** Same SKUs as Google Play (product ids are per app, so they can be identical). */
    public function getPointsForProduct(string $productId): ?int
    {
        return GooglePlayVerificationService::PRODUCT_POINTS[$productId] ?? null;
    }

    /**
     * @return array{verified:bool, transaction_id?:string, error?:string}
     */
    public function verifyPurchase(string $productId, string $receipt, string $claimedTransactionId): array
    {
        // The iOS app (StoreKit 2, the plugin default) sends the signed transaction (a JWS). It is verified against Apple's
        // root certificate: no shared secret, no call to Apple. A legacy base64 receipt still goes through verifyReceipt below.
        if (SignedTransactionVerifier::looksLikeJws($receipt)) {
            return $this->verifySignedTransaction($productId, $receipt, $claimedTransactionId);
        }

        $secret = config('services.apple_iap.shared_secret');
        if (empty($secret)) {
            Log::error('Apple IAP shared secret not configured');

            return ['verified' => false, 'error' => 'App Store purchases are not configured', 'transient' => true];
        }

        try {
            $payload = ['receipt-data' => $receipt, 'password' => $secret];
            $data = $this->post(self::PRODUCTION_URL, $payload);
            if (($data['status'] ?? null) === 21007) {
                $data = $this->post(self::SANDBOX_URL, $payload);
            }
        } catch (\Throwable $e) {
            Log::warning('Apple verifyReceipt request failed', ['error' => $e->getMessage()]);

            return ['verified' => false, 'error' => 'Could not reach the App Store, try again', 'transient' => true];
        }

        if (($data['status'] ?? -1) !== 0) {
            $status = (int) ($data['status'] ?? -1);
            Log::warning('Apple verifyReceipt non-zero status', ['status' => $status]);

            // 21005 (App Store unavailable), 21009 (internal data access error) and 2110x (Apple internal) are Apple's side and
            // worth retrying. 21002-21004/21010 etc. mean this receipt is bad or the secret is wrong (a config fix, not the user's fault).
            $transient = in_array($status, [21005, 21009], true) || ($status >= 21100 && $status <= 21199) || in_array($status, [21003, 21004], true);

            return ['verified' => false, 'error' => 'Purchase verification failed', 'transient' => $transient];
        }

        // A receipt from another app must never credit ours.
        $expectedBundle = config('services.apple_iap.bundle_id', 'com.talabna.talabna');
        if (($data['receipt']['bundle_id'] ?? null) !== $expectedBundle) {
            return ['verified' => false, 'error' => 'Receipt is not for this app'];
        }

        foreach ($data['receipt']['in_app'] ?? [] as $row) {
            if (($row['product_id'] ?? null) !== $productId
                || (string) ($row['transaction_id'] ?? '') !== $claimedTransactionId) {
                continue;
            }
            if (! empty($row['cancellation_date_ms'])) {
                return ['verified' => false, 'error' => 'Purchase was refunded'];
            }

            return ['verified' => true, 'transaction_id' => (string) $row['transaction_id']];
        }

        return ['verified' => false, 'error' => 'Purchase not found in receipt'];
    }

    /** @return array{verified:bool, transaction_id?:string, error?:string, transient?:bool} */
    private function verifySignedTransaction(string $productId, string $jws, string $claimedTransactionId): array
    {
        $result = $this->jws->verify($jws);
        if (! $result['ok']) {
            Log::warning('Apple signed transaction rejected', ['error' => $result['error'] ?? null]);

            return ['verified' => false, 'error' => 'Purchase verification failed', 'transient' => false];
        }

        $error = $this->jws->checkTransaction($result['payload'], (string) config('services.apple_iap.bundle_id', 'com.talabna.talabna'), $productId, $claimedTransactionId);
        if ($error !== null) {
            return ['verified' => false, 'error' => $error, 'transient' => false];
        }

        return ['verified' => true, 'transaction_id' => (string) $result['payload']['transactionId']];
    }

    private function post(string $url, array $payload): array
    {
        $response = Http::timeout(15)->acceptJson()->asJson()->post($url, $payload);
        if (! $response->successful()) {
            throw new \RuntimeException('Apple verifyReceipt HTTP '.$response->status());
        }

        return $response->json() ?? [];
    }
}
