<?php

namespace App\Console\Commands;

use App\Models\AiRequest;
use App\Services\Ai\AiSettler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/** Finishes or refunds unfinished AI requests, deletes old generated files, and reports anything that needs a human. */
class SettleAiRequests extends Command
{
    protected $signature = 'ai-points:settle {--prune : also delete generated files older than the retention period}';
    protected $description = 'Settle unfinished paid-AI requests (refund the ones that failed or timed out)';

    public function handle(AiSettler $settler): int
    {
        $settled = $settler->settleAll();
        $this->info("Settled {$settled} request(s).");

        $stuck = AiRequest::where('status', AiRequest::REFUND_FAILED)->count();
        if ($stuck > 0) {
            $this->error("{$stuck} request(s) could not be refunded automatically - see the AI monitor.");
        }

        if ($this->option('prune')) {
            $cutoff = now()->subDays((int) config('ai.result_days', 7));
            AiRequest::whereNotNull('result_path')->where('completed_at', '<', $cutoff)->get()->each(function (AiRequest $r) {
                Storage::disk('local')->delete($r->result_path);
                $r->update(['result_path' => null]);
            });
        }

        return $stuck > 0 ? self::FAILURE : self::SUCCESS;
    }
}
