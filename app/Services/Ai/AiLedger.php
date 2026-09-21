<?php

namespace App\Services\Ai;

use App\Exceptions\InsufficientBalanceException;
use App\Models\AiFeature;
use App\Models\AiRequest;
use App\Models\palservice_points;
use App\Models\point_transactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The only place AI points move.
 *
 *  start()   takes the points and writes the ai_requests row in ONE transaction (balance row locked), so a charge
 *            without a row, or two requests spending the same points, cannot happen.
 *  succeed() marks the request done.
 *  fail()    returns the points exactly once (request row locked, status checked) and records why.
 *
 * Every state change is a locked, status-checked transition, so the request handler, the app polling and the
 * scheduled reconciler can race each other safely.
 */
class AiLedger
{
    public const MAX_REFUND_ATTEMPTS = 5;

    /**
     * @throws InsufficientBalanceException
     * @throws AiProviderException when the feature is unknown or switched off
     */
    public function start(int $userId, string $feature, string $uuid, array $extra = []): AiRequest
    {
        $existing = AiRequest::where('user_id', $userId)->where('uuid', $uuid)->first();
        if ($existing) {
            return $existing; // a retry of the same action: never charge twice
        }

        try {
            return DB::transaction(function () use ($userId, $feature, $uuid, $extra) {
                $price = AiFeature::find($feature);
                if (! $price || ! $price->enabled) {
                    throw new AiProviderException('feature_off', 'This AI feature is not available right now.', 403);
                }
                $points = (int) $price->points_cost;

                $balance = palservice_points::where('user_id', $userId)->lockForUpdate()->first();
                $current = (int) ($balance?->point ?? 0);
                if ($points > 0 && $current < $points) {
                    throw new InsufficientBalanceException($current, $points);
                }
                if ($points > 0) {
                    $balance->decrement('point', $points);
                }

                $charge = point_transactions::create([
                    'from_user_id' => $userId,
                    'to_user_id' => $userId,
                    'type' => 'used',
                    'point' => $points,
                    'status' => 'completed',
                    'metadata' => json_encode(['reason' => 'ai', 'feature' => $feature, 'request' => $uuid]),
                ]);

                $request = AiRequest::create(array_merge([
                    'uuid' => $uuid,
                    'user_id' => $userId,
                    'feature' => $feature,
                    'points' => $points,
                    'status' => AiRequest::PROCESSING,
                    'charge_transaction_id' => $charge->id,
                ], $extra));
                $request->wasRecentlyCreated = true;

                return $request;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Two identical requests raced past the check above; the unique key stopped the second one.
            $existing = AiRequest::where('user_id', $userId)->where('uuid', $uuid)->first();
            if ($existing) {
                return $existing;
            }
            throw $e;
        }
    }

    /** @return bool false when the request was already settled (e.g. refunded as too slow) */
    public function succeed(AiRequest $request, array $result = [], ?string $path = null): bool
    {
        return DB::transaction(function () use ($request, $result, $path) {
            $row = AiRequest::whereKey($request->id)->lockForUpdate()->first();
            if (! $row || $row->status !== AiRequest::PROCESSING) {
                return false;
            }
            $row->update([
                'status' => AiRequest::SUCCEEDED,
                'result' => $result ?: null,
                'result_path' => $path,
                'completed_at' => now(),
                'duration_ms' => (int) max(0, $row->created_at->diffInMilliseconds(now())),
            ]);

            return true;
        });
    }

    /** Return the points and close the request as failed. Safe to call any number of times. */
    public function fail(AiRequest $request, string $code, string $message): void
    {
        try {
            DB::transaction(function () use ($request, $code, $message) {
                $row = AiRequest::whereKey($request->id)->lockForUpdate()->first();
                if (! $row || ! in_array($row->status, [AiRequest::PROCESSING, AiRequest::REFUND_FAILED], true)) {
                    return; // already succeeded or already refunded
                }

                $refundId = null;
                if ($row->points > 0) {
                    palservice_points::where('user_id', $row->user_id)->lockForUpdate()->first()?->increment('point', $row->points);
                    $refundId = $this->refundLedgerRow($row, $code)->id;
                }

                $row->update([
                    'status' => AiRequest::FAILED,
                    'error_code' => $code,
                    'error_message' => mb_substr($message, 0, 250),
                    'refund_transaction_id' => $refundId,
                    'refunded_at' => now(),
                    'completed_at' => now(),
                    'duration_ms' => (int) max(0, $row->created_at->diffInMilliseconds(now())),
                ]);
            });
        } catch (\Throwable $e) {
            $this->refundFailed($request, $code, $e);
        }
    }

    /**
     * The ledger row that records the refund. It is a 'refund' row; if the database does not accept that type (an older
     * schema), it is recorded as 'admin_grant' so the user still gets the points back, with the reason in the metadata.
     * Returning the points must never depend on the label of a ledger row.
     */
    private function refundLedgerRow(AiRequest $row, string $code): point_transactions
    {
        $meta = ['reason' => 'ai_failed', 'feature' => $row->feature, 'request' => $row->uuid, 'charge_transaction_id' => $row->charge_transaction_id, 'code' => $code];
        $base = ['from_user_id' => $row->user_id, 'to_user_id' => $row->user_id, 'point' => $row->points, 'status' => 'completed'];

        try {
            return point_transactions::create($base + ['type' => 'refund', 'metadata' => json_encode($meta)]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('ai.refund.ledger_type_fallback', ['request' => $row->uuid, 'error' => $e->getMessage()]);

            return point_transactions::create($base + ['type' => 'admin_grant', 'metadata' => json_encode($meta + ['ledger_type' => 'refund'])]);
        }
    }

    /** The refund itself failed. Keep the request open for retry; after a few tries flag it for an admin. */
    private function refundFailed(AiRequest $request, string $code, \Throwable $e): void
    {
        Log::critical('ai.refund.failed', ['request' => $request->uuid, 'user' => $request->user_id, 'points' => $request->points, 'error' => $e->getMessage()]);
        try {
            $row = AiRequest::find($request->id);
            if ($row && in_array($row->status, [AiRequest::PROCESSING, AiRequest::REFUND_FAILED], true)) {
                $attempts = $row->refund_attempts + 1;
                $row->update([
                    'refund_attempts' => $attempts,
                    'error_code' => $code,
                    'status' => $attempts >= self::MAX_REFUND_ATTEMPTS ? AiRequest::REFUND_FAILED : AiRequest::PROCESSING,
                ]);
            }
        } catch (\Throwable $inner) {
            Log::critical('ai.refund.bookkeeping_failed', ['request' => $request->uuid, 'error' => $inner->getMessage()]);
        }
    }

    public function balance(int $userId): int
    {
        return (int) DB::table('palservice_points')->where('user_id', $userId)->value('point');
    }
}
