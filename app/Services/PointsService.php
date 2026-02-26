<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidPinException;
use App\Exceptions\PinLockedException;
use App\Exceptions\TransferLimitExceededException;
use App\Exceptions\CountryMismatchException;
use App\Models\countries;
use App\Models\Notification;
use App\Models\palservice_points;
use App\Models\point_purchase_requests;
use App\Models\point_transactions;
use App\Models\PointPackage;
use App\Models\User;
use App\Notifications\point_purchase_notifications;
use App\Notifications\point_recived_notifications;
use App\Notifications\point_send_notifications;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PointsService
{
    // Transfer limits
    public const MAX_POINTS_PER_TRANSFER = 500;
    public const MAX_POINTS_PER_DAY = 2000;
    public const MAX_TRANSFERS_PER_DAY = 5;

    // Purchase settings
    public const AUTO_APPROVE_THRESHOLD = 500;
    public const BASE_PRICE_PER_POINT = 7.5;

    // PIN security settings
    public const PIN_LOCKOUT_DURATION_MINUTES = 30;
    public const MAX_PIN_ATTEMPTS = 3;

    protected TransferPinService $pinService;
    protected PointsAuditService $auditService;

    public function __construct(TransferPinService $pinService, PointsAuditService $auditService)
    {
        $this->pinService = $pinService;
        $this->auditService = $auditService;
    }

    /**
     * Transfer points between users with full security checks
     */
    public function transferPoints(
        int $fromUserId,
        int $toUserId,
        int $amount,
        string $pin,
        string $idempotencyKey,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        // Check for duplicate request (idempotency)
        $existingTransaction = $this->checkIdempotency($idempotencyKey, 'transfer');
        if ($existingTransaction) {
            return [
                'success' => true,
                'transaction_id' => $existingTransaction->id,
                'new_balance' => $this->getBalance($fromUserId),
                'duplicate' => true,
            ];
        }

        $fromUser = User::findOrFail($fromUserId);
        $toUser = User::findOrFail($toUserId);

        // Validate self-transfer
        if ($fromUserId === $toUserId) {
            throw new \InvalidArgumentException('Cannot transfer points to yourself');
        }

        // Validate same country
        $this->validateSameCountry($fromUser, $toUser);

        // Validate PIN
        if (!$this->pinService->hasPin($fromUser)) {
            throw new InvalidPinException('Transfer PIN not set', 0);
        }

        if ($this->pinService->isLocked($fromUser)) {
            throw new PinLockedException($fromUser->pin_locked_until);
        }

        if (!$this->pinService->verifyPin($fromUser, $pin)) {
            $attemptsRemaining = self::MAX_PIN_ATTEMPTS - $fromUser->fresh()->failed_pin_attempts;
            throw new InvalidPinException('Invalid PIN', max(0, $attemptsRemaining));
        }

        // Validate transfer limits
        $this->validateTransferLimits($fromUser, $amount);

        // Perform the transfer within a transaction with row locking
        return DB::transaction(function () use ($fromUser, $toUser, $amount, $idempotencyKey, $ipAddress, $userAgent) {
            // Lock rows in consistent order to prevent deadlocks
            $userIds = [$fromUser->id, $toUser->id];
            sort($userIds);

            foreach ($userIds as $userId) {
                palservice_points::where('user_id', $userId)->lockForUpdate()->first();
            }

            // Get sender's balance with lock
            $senderBalance = palservice_points::where('user_id', $fromUser->id)->first();

            if (!$senderBalance || $senderBalance->point < $amount) {
                throw new InsufficientBalanceException(
                    $senderBalance ? $senderBalance->point : 0,
                    $amount
                );
            }

            $balanceBefore = $senderBalance->point;

            // Deduct from sender
            $senderBalance->decrement('point', $amount);

            // Add to receiver (create record if doesn't exist)
            $receiverBalance = palservice_points::firstOrCreate(
                ['user_id' => $toUser->id],
                ['point' => 0]
            );
            $receiverBalance->increment('point', $amount);

            // Create transaction record
            $transaction = point_transactions::create([
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'type' => 'transfer',
                'point' => $amount,
                'idempotency_key' => $idempotencyKey,
                'status' => 'completed',
                'metadata' => json_encode([
                    'transfer_type' => 'user_to_user',
                ]),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            // Update daily transfer tracking
            $this->updateDailyTransferStats($fromUser, $amount);

            // Create audit logs
            $this->auditService->logTransfer($fromUser, $toUser, $amount, $transaction);

            // Send notifications
            $this->sendTransferNotifications($fromUser, $toUser, $amount);

            $newBalance = $balanceBefore - $amount;

            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'new_balance' => $newBalance,
                'duplicate' => false,
            ];
        });
    }

    /**
     * Create a point purchase request
     */
    public function purchasePoints(
        int $userId,
        int $amount,
        ?int $packageId,
        string $idempotencyKey,
        ?string $ipAddress = null
    ): array {
        // Check for duplicate request
        $existingRequest = point_purchase_requests::where('idempotency_key', $idempotencyKey)->first();
        if ($existingRequest) {
            return [
                'success' => true,
                'request_id' => $existingRequest->id,
                'client_secret' => $existingRequest->client_secret,
                'duplicate' => true,
            ];
        }

        $user = User::findOrFail($userId);

        // Get country-specific price per point
        $countryPricing = $this->getCountryPricing($user);
        $pricePerPoint = $countryPricing['price_per_point'];
        $currencyCode = $countryPricing['currency_code'];
        $currencySymbol = $countryPricing['currency_symbol'];
        $discount = 0;

        if ($packageId) {
            $package = PointPackage::active()->find($packageId);
            if ($package) {
                $amount = $package->points_amount;
                $discount = $package->discount_percentage;
                // Calculate discounted price based on country's price
                $pricePerPoint = ($pricePerPoint * (100 - $discount)) / 100;
            }
        }

        $totalPrice = $amount * $pricePerPoint;
        $autoApproved = $amount < self::AUTO_APPROVE_THRESHOLD;

        return DB::transaction(function () use ($user, $amount, $packageId, $pricePerPoint, $totalPrice, $discount, $autoApproved, $idempotencyKey, $ipAddress) {
            // Create purchase request
            $purchaseRequest = point_purchase_requests::create([
                'user_id' => $user->id,
                'points_requested' => $amount,
                'price_per_point' => $pricePerPoint,
                'total_price' => $totalPrice,
                'status' => $autoApproved ? 'approved' : 'pending',
                'idempotency_key' => $idempotencyKey,
                'auto_approved' => $autoApproved,
                'approval_type' => $autoApproved ? 'auto' : 'manual',
                'point_package_id' => $packageId,
                'discount_applied' => $discount,
            ]);

            // Only create Stripe payment intent if Stripe is configured
            $clientSecret = null;
            if (config('services.stripe.secret')) {
                try {
                    $paymentIntent = $this->createStripePaymentIntent($purchaseRequest);
                    $clientSecret = $paymentIntent->client_secret;
                    $purchaseRequest->client_secret = $clientSecret;
                    $purchaseRequest->save();
                } catch (\Exception $e) {
                    // Log error but don't fail the request - Stripe might not be configured
                    \Log::warning('Stripe payment intent creation failed: ' . $e->getMessage());
                }
            }

            // If auto-approved and no Stripe, credit points immediately
            if ($autoApproved && !$clientSecret) {
                $this->creditPurchasedPoints($purchaseRequest);
            }

            return [
                'success' => true,
                'request_id' => $purchaseRequest->id,
                'client_secret' => $clientSecret,
                'auto_approved' => $autoApproved,
                'total_price' => $totalPrice,
                'duplicate' => false,
            ];
        });
    }

    /**
     * Credit points after successful payment (called from webhook or auto-approval)
     */
    public function creditPurchasedPoints(point_purchase_requests $request): void
    {
        DB::transaction(function () use ($request) {
            $userBalance = palservice_points::where('user_id', $request->user_id)
                ->lockForUpdate()
                ->first();

            $balanceBefore = $userBalance ? $userBalance->point : 0;

            if ($userBalance) {
                $userBalance->increment('point', $request->points_requested);
            } else {
                palservice_points::create([
                    'user_id' => $request->user_id,
                    'point' => $request->points_requested,
                ]);
            }

            // Update request status if not already approved
            if ($request->status !== 'approved') {
                $request->status = 'approved';
                $request->save();
            }

            // Create transaction record
            $transaction = point_transactions::create([
                'from_user_id' => null, // System/purchase
                'to_user_id' => $request->user_id,
                'type' => 'purchase',
                'point' => $request->points_requested,
                'status' => 'completed',
                'metadata' => json_encode([
                    'purchase_request_id' => $request->id,
                    'auto_approved' => $request->auto_approved,
                ]),
            ]);

            // Log audit
            $this->auditService->logPurchase(
                User::find($request->user_id),
                $request->points_requested,
                $request
            );

            // Send notification
            $this->sendPurchaseNotification(User::find($request->user_id), $request->points_requested);
        });
    }

    /**
     * Get user's current balance
     */
    public function getBalance(int $userId, bool $withLock = false): int
    {
        $query = palservice_points::where('user_id', $userId);

        if ($withLock) {
            $query->lockForUpdate();
        }

        return $query->value('point') ?? 0;
    }

    /**
     * Get daily transfer statistics for a user
     */
    public function getDailyTransferStats(int $userId): array
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->getDefaultTransferStats();
        }

        // Reset if last transfer was on a different day
        if ($user->last_transfer_date !== now()->toDateString()) {
            return $this->getDefaultTransferStats();
        }

        $remainingCount = max(0, self::MAX_TRANSFERS_PER_DAY - $user->daily_transfer_count);
        $remainingAmount = max(0, self::MAX_POINTS_PER_DAY - $user->daily_transfer_amount);

        return [
            'max_per_transfer' => self::MAX_POINTS_PER_TRANSFER,
            'daily_limit' => self::MAX_POINTS_PER_DAY,
            'daily_transfer_limit' => self::MAX_TRANSFERS_PER_DAY,
            'transfers_today' => $user->daily_transfer_count,
            'amount_today' => $user->daily_transfer_amount,
            'remaining_transfers' => $remainingCount,
            'remaining_amount' => $remainingAmount,
        ];
    }

    /**
     * Get available point packages
     */
    public function getPackages(): \Illuminate\Database\Eloquent\Collection
    {
        return PointPackage::active()
            ->orderBy('display_order')
            ->orderBy('points_amount')
            ->get();
    }

    // ==================== Private Helper Methods ====================

    private function checkIdempotency(string $key, string $type): ?point_transactions
    {
        return point_transactions::where('idempotency_key', $key)->first();
    }

    private function validateTransferLimits(User $user, int $amount): void
    {
        // Check per-transfer limit
        if ($amount > self::MAX_POINTS_PER_TRANSFER) {
            throw new TransferLimitExceededException(
                'per_transfer',
                $amount,
                self::MAX_POINTS_PER_TRANSFER
            );
        }

        // Reset daily counters if it's a new day
        if ($user->last_transfer_date !== now()->toDateString()) {
            $user->daily_transfer_count = 0;
            $user->daily_transfer_amount = 0;
            $user->last_transfer_date = now()->toDateString();
            $user->save();
        }

        // Check daily transfer count
        if ($user->daily_transfer_count >= self::MAX_TRANSFERS_PER_DAY) {
            throw new TransferLimitExceededException(
                'daily_count',
                $user->daily_transfer_count,
                self::MAX_TRANSFERS_PER_DAY
            );
        }

        // Check daily amount limit
        if (($user->daily_transfer_amount + $amount) > self::MAX_POINTS_PER_DAY) {
            throw new TransferLimitExceededException(
                'daily_amount',
                $user->daily_transfer_amount + $amount,
                self::MAX_POINTS_PER_DAY
            );
        }
    }

    private function updateDailyTransferStats(User $user, int $amount): void
    {
        $user->daily_transfer_count++;
        $user->daily_transfer_amount += $amount;
        $user->last_transfer_date = now()->toDateString();
        $user->save();
    }

    private function getDefaultTransferStats(): array
    {
        return [
            'max_per_transfer' => self::MAX_POINTS_PER_TRANSFER,
            'daily_limit' => self::MAX_POINTS_PER_DAY,
            'daily_transfer_limit' => self::MAX_TRANSFERS_PER_DAY,
            'transfers_today' => 0,
            'amount_today' => 0,
            'remaining_transfers' => self::MAX_TRANSFERS_PER_DAY,
            'remaining_amount' => self::MAX_POINTS_PER_DAY,
        ];
    }

    private function createStripePaymentIntent(point_purchase_requests $request): \Stripe\PaymentIntent
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        return \Stripe\PaymentIntent::create([
            'amount' => (int)($request->total_price * 100), // Convert to cents
            'currency' => 'ils',
            'metadata' => [
                'purchase_request_id' => $request->id,
                'user_id' => $request->user_id,
                'points_requested' => $request->points_requested,
                'auto_approved' => $request->auto_approved ? 'true' : 'false',
            ],
        ]);
    }

    private function sendTransferNotifications(User $fromUser, User $toUser, int $amount): void
    {
        // Notification for sender
        $senderMessage = json_encode([
            'en' => "You've successfully sent $amount points to user #{$toUser->id}.",
            'ar' => "لقد أرسلت $amount نقطة إلى المستخدم #{$toUser->id} بنجاح."
        ]);

        Notification::create([
            'message' => $senderMessage,
            'user_id' => $fromUser->id,
            'type' => 'pointOut'
        ]);

        // Send FCM to sender
        if (!empty($fromUser->fcm_token)) {
            try {
                $fromUser->notify(new \App\Notifications\PointSentFcmNotification($amount, $toUser->id));
            } catch (\Exception $e) {
                Log::error('Failed to send FCM point sent notification: ' . $e->getMessage());
            }
        }

        // Notification for receiver
        $receiverMessage = json_encode([
            'en' => "You've received $amount points from user #{$fromUser->id}.",
            'ar' => "لقد استلمت $amount نقطة من المستخدم #{$fromUser->id}."
        ]);

        Notification::create([
            'message' => $receiverMessage,
            'user_id' => $toUser->id,
            'type' => 'pointIn'
        ]);

        // Send FCM notification to receiver if they have a device token
        if (!empty($toUser->fcm_token)) {
            try {
                $notification = new point_recived_notifications($fromUser, $toUser, $amount, 'Points Received');
                $toUser->notify($notification);
            } catch (\Exception $e) {
                Log::error('Failed to send FCM notification: ' . $e->getMessage());
            }
        }
    }

    private function sendPurchaseNotification(User $user, int $amount): void
    {
        $message = json_encode([
            'en' => "You have successfully purchased $amount points.",
            'ar' => "لقد اشتريت $amount نقطة بنجاح."
        ]);

        Notification::create([
            'message' => $message,
            'user_id' => $user->id,
            'type' => 'points_approved'
        ]);

        // Send FCM notification
        if (!empty($user->fcm_token)) {
            try {
                $notification = new point_purchase_notifications('approved', $amount);
                $user->notify($notification);
            } catch (\Exception $e) {
                Log::error('Failed to send FCM purchase notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Validate that both users are in the same country for transfers
     */
    private function validateSameCountry(User $fromUser, User $toUser): void
    {
        // If either user doesn't have a country set, allow the transfer
        if (empty($fromUser->country_id) || empty($toUser->country_id)) {
            return;
        }

        // Check if the sender's country allows transfers
        $fromCountry = countries::find($fromUser->country_id);
        if ($fromCountry && !$fromCountry->allow_point_transfers) {
            throw new CountryMismatchException('Point transfers are not allowed from your country');
        }

        // Check if users are in the same country
        if ($fromUser->country_id !== $toUser->country_id) {
            throw new CountryMismatchException('Point transfers are only allowed between users in the same country');
        }
    }

    /**
     * Get country-specific pricing for a user
     */
    public function getCountryPricing(User $user): array
    {
        $defaultPricing = [
            'price_per_point' => self::BASE_PRICE_PER_POINT,
            'currency_code' => 'ILS',
            'currency_symbol' => '₪',
            'currency_name' => ['en' => 'Israeli New Shekel', 'ar' => 'شيكل إسرائيلي جديد'],
            'country_id' => null,
            'country_name' => null,
        ];

        if (empty($user->country_id)) {
            return $defaultPricing;
        }

        $country = countries::find($user->country_id);
        if (!$country) {
            return $defaultPricing;
        }

        return [
            'price_per_point' => $country->price_per_point ?? self::BASE_PRICE_PER_POINT,
            'currency_code' => $country->currency_code ?? 'ILS',
            'currency_symbol' => $country->currency_symbol ?? '₪',
            'currency_name' => $country->currency_name ?? $defaultPricing['currency_name'],
            'country_id' => $country->id,
            'country_name' => $country->name,
        ];
    }

    /**
     * Check if a user can transfer to another user (same country check)
     */
    public function canTransferToUser(int $fromUserId, int $toUserId): array
    {
        $fromUser = User::find($fromUserId);
        $toUser = User::find($toUserId);

        if (!$fromUser || !$toUser) {
            return [
                'can_transfer' => false,
                'reason' => 'user_not_found',
                'message' => 'One or both users not found',
            ];
        }

        // Check if sender's country allows transfers
        if (!empty($fromUser->country_id)) {
            $fromCountry = countries::find($fromUser->country_id);
            if ($fromCountry && !$fromCountry->allow_point_transfers) {
                return [
                    'can_transfer' => false,
                    'reason' => 'transfers_disabled',
                    'message' => 'Point transfers are not allowed from your country',
                ];
            }
        }

        // If either user doesn't have a country, allow
        if (empty($fromUser->country_id) || empty($toUser->country_id)) {
            return [
                'can_transfer' => true,
                'reason' => null,
                'message' => null,
            ];
        }

        // Check same country
        if ($fromUser->country_id !== $toUser->country_id) {
            return [
                'can_transfer' => false,
                'reason' => 'country_mismatch',
                'message' => 'Point transfers are only allowed between users in the same country',
            ];
        }

        return [
            'can_transfer' => true,
            'reason' => null,
            'message' => null,
        ];
    }
}
