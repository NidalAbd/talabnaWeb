<?php

namespace App\Services\Ai;

use App\Exceptions\InsufficientBalanceException;
use App\Models\palservice_points;
use App\Models\point_transactions;
use Illuminate\Support\Facades\DB;

/**
 * Takes points for an AI action and gives them back if the action fails. Both happen inside a
 * transaction with the balance row locked, so two requests can never spend the same points.
 */
class AiPointsCharger
{
    /** @throws InsufficientBalanceException */
    public function charge(int $userId, int $points, string $feature): int
    {
        return DB::transaction(function () use ($userId, $points, $feature) {
            $balance = palservice_points::where('user_id', $userId)->lockForUpdate()->first();
            $current = (int) ($balance?->point ?? 0);
            if ($points > 0 && $current < $points) {
                throw new InsufficientBalanceException($current, $points);
            }
            if ($points > 0) {
                $balance->decrement('point', $points);
            }

            return (int) point_transactions::create([
                'from_user_id' => $userId,
                'to_user_id' => $userId,
                'type' => 'used',
                'point' => $points,
                'status' => 'completed',
                'metadata' => json_encode(['reason' => 'ai', 'feature' => $feature]),
            ])->id;
        });
    }

    /** The AI call failed: return the points and leave a ledger row saying so. */
    public function refund(int $userId, int $points, string $feature, int $chargeTransactionId): void
    {
        if ($points <= 0) {
            return;
        }
        DB::transaction(function () use ($userId, $points, $feature, $chargeTransactionId) {
            palservice_points::where('user_id', $userId)->lockForUpdate()->first()?->increment('point', $points);
            point_transactions::create([
                'from_user_id' => $userId,
                'to_user_id' => $userId,
                'type' => 'refund',
                'point' => $points,
                'status' => 'completed',
                'metadata' => json_encode(['reason' => 'ai_failed', 'feature' => $feature, 'charge_transaction_id' => $chargeTransactionId]),
            ]);
        });
    }
}
