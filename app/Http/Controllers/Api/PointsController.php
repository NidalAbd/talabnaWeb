<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidPinException;
use App\Exceptions\PinLockedException;
use App\Exceptions\TransferLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchasePointsRequest;
use App\Http\Requests\SetTransferPinRequest;
use App\Http\Requests\TransferPointsRequest;
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

    public function __construct(PointsService $pointsService, TransferPinService $pinService)
    {
        $this->pointsService = $pointsService;
        $this->pinService = $pinService;
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
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'purchase_failed',
                'message' => 'Failed to create purchase request',
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

        // Get user's country and currency
        $currency = 'ILS'; // Default
        $currencyName = ['ar' => 'شيكل', 'en' => 'ILS'];

        if ($user && $user->country_id) {
            $country = \App\Models\countries::find($user->country_id);
            if ($country) {
                $currency = $country->currency_code ?? 'ILS';
                // Map currency codes to display names
                $currencyNames = [
                    'ILS' => ['ar' => 'شيكل', 'en' => 'ILS'],
                    'EGP' => ['ar' => 'جنيه', 'en' => 'EGP'],
                    'SAR' => ['ar' => 'ريال', 'en' => 'SAR'],
                    'AED' => ['ar' => 'درهم', 'en' => 'AED'],
                    'USD' => ['ar' => 'دولار', 'en' => 'USD'],
                    'JOD' => ['ar' => 'دينار', 'en' => 'JOD'],
                    'KWD' => ['ar' => 'دينار', 'en' => 'KWD'],
                    'QAR' => ['ar' => 'ريال', 'en' => 'QAR'],
                    'BHD' => ['ar' => 'دينار', 'en' => 'BHD'],
                    'OMR' => ['ar' => 'ريال', 'en' => 'OMR'],
                    'LBP' => ['ar' => 'ليرة', 'en' => 'LBP'],
                    'SYP' => ['ar' => 'ليرة', 'en' => 'SYP'],
                    'IQD' => ['ar' => 'دينار', 'en' => 'IQD'],
                    'DZD' => ['ar' => 'دينار', 'en' => 'DZD'],
                    'MAD' => ['ar' => 'درهم', 'en' => 'MAD'],
                    'TND' => ['ar' => 'دينار', 'en' => 'TND'],
                    'LYD' => ['ar' => 'دينار', 'en' => 'LYD'],
                    'SDG' => ['ar' => 'جنيه', 'en' => 'SDG'],
                ];
                $currencyName = $currencyNames[$currency] ?? ['ar' => $currency, 'en' => $currency];
            }
        }

        return response()->json([
            'success' => true,
            'packages' => $packages->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'points_amount' => $package->points_amount,
                    'price' => $package->price,
                    'discount_percentage' => $package->discount_percentage,
                    'effective_price_per_point' => round($package->price / $package->points_amount, 4),
                    'savings' => round(($package->points_amount * PointsService::BASE_PRICE_PER_POINT) - $package->price, 2),
                    'is_popular' => $package->is_popular ?? false,
                ];
            }),
            'base_price_per_point' => PointsService::BASE_PRICE_PER_POINT,
            'currency' => $currency,
            'currency_name' => $currencyName,
        ]);
    }
}
