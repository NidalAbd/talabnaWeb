<?php

namespace App\Services;

use App\Models\palservice_points;
use App\Models\point_purchase_requests;
use App\Models\point_transactions;
use App\Models\PointsAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PointsAuditService
{
    /**
     * Log a transfer transaction (creates entries for both sender and receiver)
     */
    public function logTransfer(User $fromUser, User $toUser, int $amount, point_transactions $transaction): void
    {
        $fromBalance = palservice_points::where('user_id', $fromUser->id)->value('point') ?? 0;
        $toBalance = palservice_points::where('user_id', $toUser->id)->value('point') ?? 0;

        // Log for sender (negative amount)
        $this->createLog([
            'user_id' => $fromUser->id,
            'action' => 'transfer_sent',
            'amount' => -$amount,
            'balance_before' => $fromBalance + $amount, // Before deduction
            'balance_after' => $fromBalance,
            'reference_type' => 'point_transactions',
            'reference_id' => $transaction->id,
            'metadata' => [
                'to_user_id' => $toUser->id,
                'to_user_name' => $toUser->user_name ?? $toUser->name,
            ],
        ]);

        // Log for receiver (positive amount)
        $this->createLog([
            'user_id' => $toUser->id,
            'action' => 'transfer_received',
            'amount' => $amount,
            'balance_before' => $toBalance - $amount, // Before addition
            'balance_after' => $toBalance,
            'reference_type' => 'point_transactions',
            'reference_id' => $transaction->id,
            'metadata' => [
                'from_user_id' => $fromUser->id,
                'from_user_name' => $fromUser->user_name ?? $fromUser->name,
            ],
        ]);
    }

    /**
     * Log a purchase transaction
     */
    public function logPurchase(User $user, int $amount, point_purchase_requests $request): void
    {
        $currentBalance = palservice_points::where('user_id', $user->id)->value('point') ?? 0;

        $this->createLog([
            'user_id' => $user->id,
            'action' => 'purchase',
            'amount' => $amount,
            'balance_before' => $currentBalance - $amount,
            'balance_after' => $currentBalance,
            'reference_type' => 'point_purchase_requests',
            'reference_id' => $request->id,
            'metadata' => [
                'total_price' => $request->total_price,
                'price_per_point' => $request->price_per_point,
                'auto_approved' => $request->auto_approved,
                'package_id' => $request->point_package_id,
            ],
        ]);
    }

    /**
     * Log a refund transaction
     */
    public function logRefund(User $user, int $amount, string $reason, ?point_purchase_requests $request = null): void
    {
        $currentBalance = palservice_points::where('user_id', $user->id)->value('point') ?? 0;

        $this->createLog([
            'user_id' => $user->id,
            'action' => 'refund',
            'amount' => -$amount,
            'balance_before' => $currentBalance + $amount,
            'balance_after' => $currentBalance,
            'reference_type' => $request ? 'point_purchase_requests' : null,
            'reference_id' => $request?->id,
            'metadata' => [
                'reason' => $reason,
            ],
        ]);
    }

    /**
     * Log an admin adjustment
     */
    public function logAdminAdjustment(
        User $user,
        int $amount,
        User $admin,
        string $reason,
        string $adjustmentType = 'grant' // 'grant' or 'deduct'
    ): void {
        $currentBalance = palservice_points::where('user_id', $user->id)->value('point') ?? 0;

        $this->createLog([
            'user_id' => $user->id,
            'action' => $adjustmentType === 'grant' ? 'admin_grant' : 'admin_deduct',
            'amount' => $adjustmentType === 'grant' ? $amount : -$amount,
            'balance_before' => $adjustmentType === 'grant'
                ? $currentBalance - $amount
                : $currentBalance + $amount,
            'balance_after' => $currentBalance,
            'metadata' => [
                'admin_id' => $admin->id,
                'admin_name' => $admin->user_name ?? $admin->name,
                'reason' => $reason,
            ],
        ]);
    }

    /**
     * Log a badge purchase (points used)
     */
    public function logBadgeUsage(User $user, int $amount, int $servicePostId, string $badgeType): void
    {
        $currentBalance = palservice_points::where('user_id', $user->id)->value('point') ?? 0;

        $this->createLog([
            'user_id' => $user->id,
            'action' => 'badge_purchase',
            'amount' => -$amount,
            'balance_before' => $currentBalance + $amount,
            'balance_after' => $currentBalance,
            'metadata' => [
                'service_post_id' => $servicePostId,
                'badge_type' => $badgeType,
            ],
        ]);
    }

    /**
     * Get audit log history for a user
     */
    public function getUserHistory(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return PointsAuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs by action type
     */
    public function getByAction(string $action, int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return PointsAuditLog::where('action', $action)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create an audit log entry
     */
    private function createLog(array $data): void
    {
        try {
            PointsAuditLog::create([
                'user_id' => $data['user_id'],
                'action' => $data['action'],
                'amount' => $data['amount'],
                'balance_before' => $data['balance_before'],
                'balance_after' => $data['balance_after'],
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            Log::error('Failed to create audit log: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e,
            ]);
        }
    }
}
