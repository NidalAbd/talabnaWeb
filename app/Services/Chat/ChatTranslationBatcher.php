<?php

namespace App\Services\Chat;

use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Translates messages from all chats together. A sent message that the other
 * person needs in another language is marked `translate_to`; right after the
 * send's response we wait a moment so messages sent at the same time by other
 * users pile up, then one request (whoever gets the lock) translates every
 * pending message in a single API call, each into its own language, and saves
 * each translation on its message.
 */
class ChatTranslationBatcher
{
    /** Wait for other messages to join the batch. */
    public const GATHER_MS = 1500;

    /** Most messages in one API call. */
    public const MAX_BATCH = 40;

    public function __construct(
        private readonly ChatLanguageService $lang,
        private readonly ChatTranslationQuota $quota,
    ) {
    }

    /** Run after the send response: gather, then translate everything pending. */
    public function flushSoon(): void
    {
        usleep(self::GATHER_MS * 1000);
        $this->flush();
    }

    public function flush(): void
    {
        // One batch at a time; a request that finds the lock taken leaves its
        // message to the batch already running (or the next one).
        Cache::lock('chat:translate-batch', 60)->get(function () {
            for ($round = 0; $round < 5; $round++) {
                $pending = Message::whereNotNull('translate_to')->orderBy('id')->limit(self::MAX_BATCH)->get();
                if ($pending->isEmpty()) return;

                $items = [];
                foreach ($pending as $m) {
                    $items[$m->id] = ['text' => (string) $m->body, 'to' => $m->translate_to];
                }
                $done = $this->lang->translateBatch($items);

                foreach ($pending as $m) {
                    $to = $m->translate_to;
                    $m->translate_to = null;
                    if (isset($done[$m->id])) {
                        $m->rememberTranslation($to, $done[$m->id]); // saves
                    } else {
                        $m->saveQuietly();
                        // Paid at send time; give it back. Reading the chat retries it.
                        $reader = $m->conversation?->otherUser($m->sender_id);
                        if ($reader) $this->quota->refund($reader->id);
                    }
                }
                Log::info('chat translate batch', ['messages' => $pending->count(), 'ok' => count($done)]);
            }
        });
    }
}
