<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CountryMismatchException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidPinException;
use App\Exceptions\PinLockedException;
use App\Exceptions\TransferLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchasePointsRequest;
use App\Http\Requests\SetTransferPinRequest;
use App\Http\Requests\TransferPointsRequest;
use App\Services\GooglePlayVerificationService;
use App\Services\PointsService;
use App\Services\TransferPinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PointsController extends Controller
{
    protected PointsService $pointsService;
    protected TransferPinService $pinService;
    protected GooglePlayVerificationService $googlePlayService;

    public function __construct(
        PointsService $pointsService,
        TransferPinService $pinService,
        GooglePlayVerificationService $googlePlayService
    ) {
        $this->pointsService = $pointsService;
        $this->pinService = $pinService;
        $this->googlePlayService = $googlePlayService;
    }

    /**
     * Transfer points to another user (POST /api/points/transfer)
     */
    public function transfer(TransferPointsRequest $request): JsonResponse
    {
        try {
            $result = $this->pointsService->transferPoints(
                fromUserId: $request->user()->id,
                toUserId: $request->validated('to_user_id'),
                amount: $request->validated('amount'),
                pin: $request->validated('pin'),
                idempotencyKey: $request->validated('idempotency_key'),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'new_balance' => $result['new_balance'],
                'duplicate' => $result['duplicate'] ?? false,
                'message' => $result['duplicate']
                    ? 'Duplicate request - returning cached result'
                    : 'Transfer completed successfully',
            ]);

        } catch (InsufficientBalanceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'insufficient_balance',
                'message' => 'Insufficient balance for this transfer',
                'details' => $e->getDetails(),
            ], 422);

        } catch (TransferLimitExceededException $e) {
            return response()->json([
                'success' => false,
                'error' => 'limit_exceeded',
                'message' => $e->getMessage(),
                'details' => $e->getDetails(),
            ], 422);

        } catch (InvalidPinException $e) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_pin',
                'message' => $e->getMessage(),
                'attempts_remaining' => $e->getAttemptsRemaining(),
            ], 401);

        } catch (PinLockedException $e) {
            return response()->json([
                'success' => false,
                'error' => 'pin_locked',
                'message' => 'PIN is locked due to too many failed attempts',
                'locked_until' => $e->getLockedUntil()->toIso8601String(),
                'remaining_minutes' => $e->getRemainingMinutes(),
            ], 423);

        } catch (CountryMismatchException $e) {
            return response()->json([
                'success' => false,
                'error' => 'country_mismatch',
                'message' => $e->getMessage(),
            ], 403);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_request',
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            Log::error('Transfer failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'to_user_id' => $request->validated('to_user_id'),
                'amount' => $request->validated('amount'),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'transfer_failed',
                'message' => 'An error occurred while processing the transfer',
            ], 500);
        }
    }

    /**
     * Get current balance (GET /api/points/balance)
     */
    public function balance(Request $request): JsonResponse
    {
        $balance = $this->pointsService->getBalance($request->user()->id);

        return response()->json([
            'success' => true,
            'balance' => $balance,
        ]);
    }

    /**
     * Get transfer limits and daily stats (GET /api/points/transfer-limits)
     */
    public function transferLimits(Request $request): JsonResponse
    {
        $stats = $this->pointsService->getDailyTransferStats($request->user()->id);

        return response()->json([
            'success' => true,
            ...$stats,
        ]);
    }

    /**
     * Set transfer PIN (POST /api/points/pin/set)
     */
    public function setPin(SetTransferPinRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user already has a PIN set
            $isUpdate = $this->pinService->hasPin($user);

            $this->pinService->setPin($user, $request->validated('pin'));

            return response()->json([
                'success' => true,
                'message' => $isUpdate ? 'Transfer PIN updated successfully' : 'Transfer PIN set successfully',
                'is_update' => $isUpdate,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to set PIN: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'pin_set_failed',
                'message' => 'Failed to set transfer PIN',
            ], 500);
        }
    }

    /**
     * Verify transfer PIN (POST /api/points/pin/verify)
     */
    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        $user = $request->user();

        // Check if locked
        if ($this->pinService->isLocked($user)) {
            $lockoutTime = $this->pinService->getLockoutTime($user);
            return response()->json([
                'success' => false,
                'valid' => false,
                'error' => 'pin_locked',
                'locked_until' => $lockoutTime?->toIso8601String(),
            ], 423);
        }

        // Verify PIN
        $isValid = $this->pinService->verifyPin($user, $request->pin);

        if (!$isValid) {
            $remainingAttempts = $this->pinService->getRemainingAttempts($user->fresh());

            return response()->json([
                'success' => false,
                'valid' => false,
                'error' => 'invalid_pin',
                'attempts_remaining' => $remainingAttempts,
            ], 401);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
        ]);
    }

    /**
     * Get PIN status (GET /api/points/pin/status)
     */
    public function pinStatus(Request $request): JsonResponse
    {
        $status = $this->pinService->getPinStatus($request->user());

        return response()->json([
            'success' => true,
            ...$status,
        ]);
    }

    /**
     * Create purchase request (POST /api/purchase/create)
     */
    public function purchase(PurchasePointsRequest $request): JsonResponse
    {
        try {
            $result = $this->pointsService->purchasePoints(
                userId: $request->user()->id,
                amount: $request->validated('points_amount'),
                packageId: $request->validated('package_id'),
                idempotencyKey: $request->validated('idempotency_key'),
                ipAddress: $request->ip()
            );

            return response()->json([
                'success' => true,
                'request_id' => $result['request_id'],
                'client_secret' => $result['client_secret'],
                'auto_approved' => $result['auto_approved'],
                'total_price' => $result['total_price'],
                'duplicate' => $result['duplicate'] ?? false,
                'message' => $result['auto_approved']
                    ? 'Purchase will be auto-approved upon payment'
                    : 'Purchase request created, awaiting admin approval after payment',
            ]);

        } catch (\Exception $e) {
            Log::error('Purchase request failed: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'amount' => $request->validated('points_amount'),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return actual error in response for debugging
            return response()->json([
                'success' => false,
                'error' => 'purchase_failed',
                'message' => 'Failed to create purchase request',
                'debug' => config('app.debug') ? [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Get available point packages (GET /api/points/packages)
     */
    public function packages(): JsonResponse
    {
        $packages = $this->pointsService->getPackages();
        $user = Auth::user();

        // Get country-specific pricing
        $pricing = $user
            ? $this->pointsService->getCountryPricing($user)
            : [
                'price_per_point' => PointsService::BASE_PRICE_PER_POINT,
                'currency_code' => 'ILS',
                'currency_symbol' => '₪',
                'currency_name' => ['en' => 'Israeli New Shekel', 'ar' => 'شيكل إسرائيلي جديد'],
            ];

        $basePricePerPoint = $pricing['price_per_point'];

        return response()->json([
            'success' => true,
            'packages' => $packages->map(function ($package) use ($basePricePerPoint) {
                // Calculate price based on country's base price and package discount
                $discountedPrice = $package->points_amount * $basePricePerPoint * (100 - $package->discount_percentage) / 100;

                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'points_amount' => $package->points_amount,
                    'price' => round($discountedPrice, 2),
                    'original_price' => round($package->points_amount * $basePricePerPoint, 2),
                    'discount_percentage' => $package->discount_percentage,
                    'effective_price_per_point' => round($discountedPrice / $package->points_amount, 4),
                    'savings' => round(($package->points_amount * $basePricePerPoint) - $discountedPrice, 2),
                    'is_popular' => $package->is_popular ?? false,
                ];
            }),
            'base_price_per_point' => $basePricePerPoint,
            'currency_code' => $pricing['currency_code'],
            'currency_symbol' => $pricing['currency_symbol'],
            'currency_name' => $pricing['currency_name'],
        ]);
    }

    /**
     * Check if user can transfer to another user (GET /api/points/can-transfer/{toUserId})
     */
    public function canTransfer(Request $request, int $toUserId): JsonResponse
    {
        $result = $this->pointsService->canTransferToUser($request->user()->id, $toUserId);

        return response()->json([
            'success' => true,
            'can_transfer' => $result['can_transfer'],
            'reason' => $result['reason'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Get user's pricing info (GET /api/points/pricing)
     */
    public function pricing(Request $request): JsonResponse
    {
        $user = $request->user();
        $pricing = $this->pointsService->getCountryPricing($user);

        return response()->json([
            'success' => true,
            'price_per_point' => $pricing['price_per_point'],
            'currency_code' => $pricing['currency_code'],
            'currency_symbol' => $pricing['currency_symbol'],
            'currency_name' => $pricing['currency_name'],
            'country_id' => $pricing['country_id'],
            'country_name' => $pricing['country_name'],
        ]);
    }

    /**
     * Verify Google Play purchase and credit points (POST /api/points/google-verify)
     */
    public function verifyGooglePurchase(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|string',
            'purchase_token' => 'required|string',
            'order_id' => 'nullable|string',
        ]);

        $user = $request->user();
        $productId = $request->input('product_id');
        $purchaseToken = $request->input('purchase_token');
        $orderId = $request->input('order_id', '');

        // Check points amount for this product
        $pointsAmount = $this->googlePlayService->getPointsForProduct($productId);
        if ($pointsAmount === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID',
            ], 400);
        }

        // Prevent duplicate processing using order_id
        if ($orderId) {
            $existing = \App\Models\point_purchase_requests::where('google_order_id', $orderId)->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase already processed',
                    'points' => $pointsAmount,
                    'duplicate' => true,
                ]);
            }
        }

        // Verify with Google Play
        $verification = $this->googlePlayService->verifyPurchase($productId, $purchaseToken);

        if (!$verification['verified']) {
            Log::warning('Google Play purchase verification failed', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $verification['error'] ?? 'unknown',
            ]);

            return response()->json([
                'success' => false,
                'message' => $verification['error'] ?? 'Purchase verification failed',
            ], 422);
        }

        try {
            // Credit points via the existing service
            $this->pointsService->creditGooglePlayPurchase(
                userId: $user->id,
                pointsAmount: $pointsAmount,
                productId: $productId,
                orderId: $orderId ?: ($verification['order_id'] ?? ''),
                purchaseToken: $purchaseToken
            );

            // Acknowledge the purchase with Google
            $this->googlePlayService->acknowledgePurchase($productId, $purchaseToken);

            return response()->json([
                'success' => true,
                'message' => 'Points credited successfully',
                'points' => $pointsAmount,
                'duplicate' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to credit Google Play purchase', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to credit points',
            ], 500);
        }
    }
}
