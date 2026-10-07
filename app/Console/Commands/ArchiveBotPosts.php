<?php

namespace App\Console\Commands;

use App\Models\ServicePost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Hides the AI sample posts made by `ai:seed` bot accounts from the app, the website and Google by moving them from
 * "published" to "archive" (2026-10-08, Nidal: show real posts only). Nothing is deleted; --restore publishes them
 * again.
 */
class ArchiveBotPosts extends Command
{
    protected $signature = 'posts:archive-bot-posts {--restore : Publish the archived bot posts again} {--dry-run : Only count}';

    protected $description = 'Archive (or restore) the posts written by @bot.talabna.com accounts';

    public function handle(): int
    {
        $botIds = DB::table('users')->where('email', 'like', '%' . ServicePost::BOT_EMAIL_DOMAIN)->pluck('id');
        [$from, $to] = $this->option('restore') ? ['archive', 'published'] : ['published', 'archive'];

        $query = DB::table('service_posts')->whereIn('user_id', $botIds)->where('state', $from);
        $count = $query->count();
        $this->info("Bot accounts: {$botIds->count()}; posts {$from} -> {$to}: {$count}");
        if ($this->option('dry-run') || $count === 0) {
            return self::SUCCESS;
        }

        // updated_at untouched so the posts keep their dates if they are restored.
        $changed = $query->update(['state' => $to]);
        $this->info("Changed: {$changed}");

        return self::SUCCESS;
    }
}
