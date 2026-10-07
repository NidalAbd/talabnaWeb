<?php

namespace App\Services\Ai;

use App\Exceptions\InsufficientBalanceException;
use App\Models\AiFeature;
use App\Models\AiRequest;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Services\SubscriptionService;
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

                // Subscription allowances (2026-10-02: Basic users were still
                // charged points for the AI images their plan includes).
                $cover = $this->coverage($userId, $feature);
                if ($cover !== null) {
                    $points = 0;
                    if ($cover['usage'] !== null) {
                        app(SubscriptionService::class)->useFeature($userId, $cover['usage']);
                    }
                }

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
                    'metadata' => json_encode(['reason' => 'ai', 'feature' => $feature, 'request' => $uuid]
                        + ($cover !== null ? ['covered_by' => 'subscription', 'covered_usage' => $cover['usage']] : [])),
                ]);

                // Only real columns: callers pass extra context (mode, style, seconds...) that has no column, and
                // AiRequest is unguarded, so those keys made the insert fail (a 500 for every Photo Studio tool).
                $extra = array_intersect_key($extra, array_flip(self::EXTRA_COLUMNS));
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

    /**
     * Features a subscription plan includes: feature key => [plan feature key, usage counter or null].
     * A counter means a monthly allowance (ai_images_per_month); null means unlimited while the plan has it.
     */
    /** Columns of ai_requests a caller may fill through start()'s $extra. */
    private const EXTRA_COLUMNS = ['provider', 'provider_job_id', 'prompt', 'ip'];

    private const PLAN_COVERS = [
        'generate_image' => ['ai_images_per_month', 'ai_images_used'],
        // Release B: the Photo Studio spends the same monthly image credits; Snap to sell comes with Pro/Business.
        'studio_light' => ['ai_images_per_month', 'ai_images_used'],
        'studio_background' => ['ai_images_per_month', 'ai_images_used'],
        'studio_scene' => ['ai_images_per_month', 'ai_images_used'],
        'studio_cinematic' => ['ai_images_per_month', 'ai_images_used'],
        'snap_to_sell' => ['snap_to_sell', null],
        // Release C: Business includes 2 AI videos a month (from a prompt or from a photo).
        'generate_video' => ['ai_videos_per_month', 'ai_videos_used'],
        'studio_video' => ['ai_videos_per_month', 'ai_videos_used'],
        'translate_post' => ['auto_translate_posts', null],
    ];

    /** ['usage' => ?string] when the user's active plan covers this feature now, else null. */
    public function coverage(int $userId, string $feature): ?array
    {
        $map = self::PLAN_COVERS[$feature] ?? null;
        if ($map === null) {
            return null;
        }
        [$limitKey, $usageKey] = $map;
        $subs = app(SubscriptionService::class);
        $ok = $usageKey === null
            ? $subs->hasFeature($userId, $limitKey)
            : $subs->canUseFeature($userId, $limitKey, $usageKey);
        return $ok ? ['usage' => $usageKey] : null;
    }

    /** Remaining plan uses of a feature (null = not covered, -1 = unlimited). */
    public function remainingAllowance(int $userId, string $feature): ?int
    {
        $map = self::PLAN_COVERS[$feature] ?? null;
        if ($map === null || $this->coverage($userId, $feature) === null) {
            return null;
        }
        [$limitKey, $usageKey] = $map;
        if ($usageKey === null) {
            return -1;
        }
        $sub = app(SubscriptionService::class)->getActiveSubscription($userId);
        $limit = (int) ($sub?->getFeature($limitKey, 0) ?? 0);
        $used = (int) (($sub?->usage ?? [])[$usageKey] ?? 0);
        return max(0, $limit - $used);
    }

    private function returnAllowance(AiRequest $row): void
    {
        if (! $row->charge_transaction_id) {
            return;
        }
        $charge = point_transactions::find($row->charge_transaction_id);
        $meta = json_decode((string) ($charge?->metadata ?? ''), true) ?: [];
        if (($meta['covered_by'] ?? null) === 'subscription' && ! empty($meta['covered_usage'])) {
            app(SubscriptionService::class)->useFeature($row->user_id, $meta['covered_usage'], -1);
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

                // A plan-covered request gives its allowance back instead of points.
                $this->returnAllowance($row);

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
