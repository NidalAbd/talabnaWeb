<?php

namespace App\Console\Commands;

use App\Models\ServicePost;
use App\Services\PostAttributes;
use App\Services\PostSearch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/** Fills search_text and post_attribute_values for every post; then search uses the FULLTEXT index (PostSearch). */
class IndexPostSearch extends Command
{
    protected $signature = 'posts:index-search {--chunk=500}';

    protected $description = 'Index every post for search and detail filters';

    public function handle(): int
    {
        $n = 0;
        ServicePost::query()->withoutGlobalScopes()->select(['id', 'title', 'description', 'details', 'categories_id'])
            ->orderBy('id')->chunkById((int) $this->option('chunk'), function ($posts) use (&$n) {
                foreach ($posts as $p) {
                    PostSearch::indexPost($p);
                    $n++;
                }
            });
        Cache::forever(PostSearch::READY_KEY, now()->toIso8601String());
        $this->info("Indexed $n posts");

        return self::SUCCESS;
    }
}
