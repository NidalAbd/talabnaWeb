<?php

namespace App\Services\Ai;

use App\Models\AiRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Brings unfinished requests to an end so no user is ever left charged for nothing:
 *   - a video job is polled; done -> succeeded, failed -> refund;
 *   - anything still processing after its time limit (a crashed request, a lost job) is refunded;
 *   - a refund that failed earlier is retried.
 * Runs from the scheduler every minute and also when the app asks about a request or opens the AI prices.
 */
class AiSettler
{
    public function __construct(private AiLedger $ledger, private AiMediaService $media)
    {
    }

    /** @return int how many requests were settled */
    public function settleAll(): int
    {
        $count = 0;
        AiRequest::where('status', AiRequest::PROCESSING)->orderBy('id')->limit(200)->get()
            ->each(function (AiRequest $r) use (&$count) {
                $count += $this->settle($r) ? 1 : 0;
            });

        // A refund that could not be made is retried every 10 minutes until it works (never left with the user out of pocket).
        AiRequest::where('status', AiRequest::REFUND_FAILED)->where('updated_at', '<', now()->subMinutes(10))->limit(50)->get()
            ->each(function (AiRequest $r) use (&$count) {
                $this->ledger->fail($r, $r->error_code ?: 'refund_retry', $r->error_message ?: 'Refund retried');
                $r->touch();
                $count++;
            });

        return $count;
    }

    public function settleForUser(int $userId): void
    {
        AiRequest::where('user_id', $userId)->where('status', AiRequest::PROCESSING)->limit(20)->get()
            ->each(fn (AiRequest $r) => $this->settle($r));
    }

    /** @return bool true if the request reached a final state */
    public function settle(AiRequest $r): bool
    {
        $r->refresh();
        if ($r->status !== AiRequest::PROCESSING) {
            return false;
        }

        // A refund that failed earlier: try again right away.
        if ($r->refund_attempts > 0) {
            $this->ledger->fail($r, $r->error_code ?: 'refund_retry', 'Refund retried');

            return true;
        }

        if ($r->kind() === 'video' && $r->provider_job_id) {
            $poll = $this->media->pollVideo($r->provider_job_id, $r->uuid);
            if ($poll['state'] === 'done') {
                if (! $this->ledger->succeed($r, [], $poll['path'])) {
                    Storage::disk('local')->delete($poll['path']); // it was refunded meanwhile
                }

                return true;
            }
            if ($poll['state'] === 'failed') {
                $this->ledger->fail($r, $poll['code'] ?? 'provider_error', $poll['message'] ?? 'The AI could not finish the video.');

                return true;
            }
        }

        $limit = (int) (config('ai.stale_minutes')[$r->kind()] ?? [
            'text' => 3, 'image' => 6, 'video' => 20,
        ][$r->kind()]);
        if ($r->created_at->lt(now()->subMinutes($limit))) {
            Log::warning('ai.request.timed_out', ['request' => $r->uuid, 'feature' => $r->feature]);
            $this->ledger->fail($r, 'timeout', 'The AI took too long.');

            return true;
        }

        return false;
    }
}
