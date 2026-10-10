<?php

namespace App\Jobs;

use App\Services\Social\ActivityDigest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** The summary push at the end of a digest window (ActivityDigest). */
class SendSocialDigest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 2;

    public function __construct(public int $recipientId, public string $kind, public int $postId) {}

    public function handle(): void
    {
        ActivityDigest::flush($this->recipientId, $this->kind, $this->postId);
    }
}
