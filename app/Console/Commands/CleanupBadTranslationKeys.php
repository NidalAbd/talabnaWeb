<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\TranslationLoader\LanguageLine;

class CleanupBadTranslationKeys extends Command
{
    protected $signature = 'translations:cleanup-bad-keys';
    protected $description = 'Delete translation keys that are not real user-facing text (e.g., code fragments, Blade expressions)';

    public function handle()
    {
        $query = LanguageLine::query()
            ->where('key', 'like', '%}}%')
            ->orWhere('key', 'like', '%->%')
            ->orWhere('key', 'regexp', '[()]+');
        $count = $query->count();
        if ($count === 0) {
            $this->info('No bad keys found.');
            return;
        }
        $this->info("Deleting $count bad translation keys...");
        $query->delete();
        $this->info('Cleanup complete.');
    }
} 