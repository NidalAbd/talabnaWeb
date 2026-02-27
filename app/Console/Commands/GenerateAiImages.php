<?php

namespace App\Console\Commands;

use App\Models\Categories;
use App\Models\Sub_categories;
use App\Services\DalleImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateAiImages extends Command
{
    protected $signature = 'ai:generate {type : category or subcategory}';
    protected $description = 'Generate AI images for categories or subcategories with rate-limit-safe 65s delay';

    protected DalleImageService $dalleService;

    public function __construct(DalleImageService $dalleService)
    {
        parent::__construct();
        $this->dalleService = $dalleService;
    }

    public function handle(): int
    {
        $type = $this->argument('type');

        if (!in_array($type, ['category', 'subcategory'])) {
            $this->error('Type must be "category" or "subcategory".');
            return Command::FAILURE;
        }

        $progressFile = "ai_generate_progress_{$type}.json";
        $progress = $this->loadProgress($progressFile);

        $choice = $this->choice(
            'Start from beginning or continue from last?',
            ['start', 'continue'],
            $progress ? 'continue' : 'start'
        );

        if ($choice === 'start') {
            $progress = ['last_id' => 0, 'completed' => [], 'errors' => []];
            $this->saveProgress($progressFile, $progress);
        }

        $lastId = $progress['last_id'] ?? 0;

        if ($type === 'category') {
            $items = Categories::where('id', '>', $lastId)->orderBy('id', 'asc')->get();
        } else {
            $items = Sub_categories::with('category')->where('id', '>', $lastId)->orderBy('id', 'asc')->get();
        }

        if ($items->isEmpty()) {
            $this->info('No items to process. All done!');
            return Command::SUCCESS;
        }

        $this->info("Found {$items->count()} {$type}(s) to process (after ID {$lastId}).");
        $this->newLine();

        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        foreach ($items as $index => $item) {
            $nameEn = $item->name['en'] ?? '';
            $nameAr = $item->name['ar'] ?? '';
            $displayName = $nameEn ?: $nameAr;

            $bar->setMessage("Processing: {$displayName} (ID: {$item->id})");

            try {
                if ($type === 'category') {
                    $success = $this->dalleService->generateForCategory($item);
                } else {
                    $success = $this->dalleService->generateForSubcategory($item);
                }

                if ($success) {
                    $progress['completed'][] = ['id' => $item->id, 'name' => $displayName];
                    $this->newLine();
                    $this->info("  ✓ Generated image for \"{$displayName}\" (ID: {$item->id})");
                } else {
                    $error = $this->dalleService->getLastError() ?: 'Unknown error';
                    $progress['errors'][] = ['id' => $item->id, 'name' => $displayName, 'error' => $error];
                    $this->newLine();
                    $this->warn("  ✗ Failed for \"{$displayName}\" (ID: {$item->id}): {$error}");
                }
            } catch (\Exception $e) {
                $progress['errors'][] = ['id' => $item->id, 'name' => $displayName, 'error' => $e->getMessage()];
                $this->newLine();
                $this->error("  ✗ Exception for \"{$displayName}\" (ID: {$item->id}): {$e->getMessage()}");
            }

            $progress['last_id'] = $item->id;
            $this->saveProgress($progressFile, $progress);

            $bar->advance();

            // Sleep 65s between images (skip after last item)
            if ($index < $items->count() - 1) {
                $this->newLine();
                $this->info("  Waiting 65 seconds for rate limit...");
                sleep(65);
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("=== Generation Complete ===");
        $this->info("Completed: " . count($progress['completed']));
        $this->info("Errors: " . count($progress['errors']));

        if (!empty($progress['errors'])) {
            $this->newLine();
            $this->warn("Failed items:");
            foreach ($progress['errors'] as $err) {
                $this->warn("  - {$err['name']} (ID: {$err['id']}): {$err['error']}");
            }
        }

        return Command::SUCCESS;
    }

    protected function loadProgress(string $file): ?array
    {
        if (Storage::disk('local')->exists($file)) {
            $content = Storage::disk('local')->get($file);
            return json_decode($content, true);
        }
        return null;
    }

    protected function saveProgress(string $file, array $progress): void
    {
        Storage::disk('local')->put($file, json_encode($progress, JSON_PRETTY_PRINT));
    }
}
