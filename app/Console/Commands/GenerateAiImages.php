<?php

namespace App\Console\Commands;

use App\Models\Categories;
use App\Models\Sub_categories;
use App\Services\DalleImageService;
use App\Traits\LogsCommandExecution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateAiImages extends Command
{
    use LogsCommandExecution;

    protected $signature = 'ai:generate
        {type? : category or subcategory (omit to generate both)}
        {--category= : Filter subcategories by parent category ID}
        {--auto : Run non-interactively, auto-continue from last progress (for cron/scheduler)}
        {--fresh : When used with --auto, start from beginning instead of continuing}';
    protected $description = 'Generate AI images for categories and/or subcategories with rate-limit-safe 65s delay';

    protected DalleImageService $dalleService;

    public function __construct(DalleImageService $dalleService)
    {
        parent::__construct();
        $this->dalleService = $dalleService;
    }

    public function handle(): int
    {
        $type = $this->argument('type');

        if ($type && !in_array($type, ['category', 'subcategory'])) {
            $this->error('Type must be "category", "subcategory", or omit for both.');
            return Command::FAILURE;
        }

        $this->logStart(['type' => $type ?? 'both']);

        try {
            // If no type specified, run both sequentially
            if (!$type) {
                $this->renderHeader('AI Image Generator — Categories + Subcategories');

                $catResult = $this->processType('category');
                $subResult = $this->processType('subcategory');

                $this->newLine();
                $this->renderFinalSummary();

                $totalCompleted = $this->countCompletedFromProgress();
                $this->logFinish($totalCompleted);

                return ($catResult === Command::SUCCESS && $subResult === Command::SUCCESS)
                    ? Command::SUCCESS
                    : Command::FAILURE;
            }

            $this->renderHeader('AI Image Generator — ' . ucfirst($type));
            $result = $this->processType($type);

            $this->newLine();
            $this->renderFinalSummary();

            $totalCompleted = $this->countCompletedFromProgress();
            $this->logFinish($totalCompleted);

            return $result;
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            throw $e;
        }
    }

    protected function countCompletedFromProgress(): int
    {
        $count = 0;
        $catProgress = $this->loadProgress('ai_generate_progress_category.json');
        $subProgress = $this->loadProgress('ai_generate_progress_subcategory.json');

        if ($catProgress) {
            $count += count($catProgress['completed'] ?? []);
        }
        if ($subProgress) {
            $count += count($subProgress['completed'] ?? []);
        }

        return $count;
    }

    protected function processType(string $type): int
    {
        $categoryId = $this->option('category');

        // Use a separate progress file when filtering by category
        if ($type === 'subcategory' && $categoryId) {
            $progressFile = "ai_generate_progress_subcategory_cat{$categoryId}.json";
        } else {
            $progressFile = "ai_generate_progress_{$type}.json";
        }

        $progress = $this->loadProgress($progressFile);

        $isAuto = $this->option('auto');

        $this->newLine();

        // Build section header with optional category name
        if ($type === 'subcategory' && $categoryId) {
            $parentCategory = Categories::find($categoryId);
            if (!$parentCategory) {
                $this->error("Category with ID {$categoryId} not found.");
                return Command::FAILURE;
            }
            $catName = $parentCategory->name['en'] ?? $parentCategory->name['ar'] ?? "ID:{$categoryId}";
            $this->renderSectionHeader("📂 SUBCATEGORIES (Category: {$catName})");
        } else {
            $this->renderSectionHeader($type === 'category' ? '📁 CATEGORIES' : '📂 SUBCATEGORIES');
        }

        if ($isAuto) {
            // Non-interactive: auto-continue from last, or start fresh with --fresh
            $choice = $this->option('fresh') ? 'start' : 'continue';
            $this->info("  [Auto mode] " . ($choice === 'start' ? 'Starting from beginning' : 'Continuing from last progress'));
        } else {
            $choice = $this->choice(
                "[$type] Start from beginning or continue from last?",
                ['start', 'continue'],
                $progress ? 'continue' : 'start'
            );
        }

        if ($choice === 'start') {
            $progress = ['last_id' => 0, 'completed' => [], 'errors' => []];
            $this->saveProgress($progressFile, $progress);
        }

        // Ensure all keys exist (handles legacy/incomplete progress files)
        $progress = array_merge(['last_id' => 0, 'completed' => [], 'errors' => []], $progress ?? []);

        $lastId = $progress['last_id'] ?? 0;

        if ($type === 'category') {
            $items = Categories::where('id', '>', $lastId)->orderBy('id', 'asc')->get();
        } else {
            $query = Sub_categories::with('category')->where('id', '>', $lastId);
            if ($categoryId) {
                $query->where('categories_id', $categoryId);
            }
            $items = $query->orderBy('id', 'asc')->get();
        }

        if ($items->isEmpty()) {
            $this->info("  No {$type} items to process. All done!");
            return Command::SUCCESS;
        }

        $total = $items->count();
        $alreadyDone = count($progress['completed'] ?? []);

        $this->info("  Found {$total} {$type}(s) remaining (after ID {$lastId}).");
        $this->newLine();

        // Show items table before starting
        $this->renderItemsTable($type, $items);
        $this->newLine();

        foreach ($items as $index => $item) {
            $current = $index + 1;
            $nameEn = $item->name['en'] ?? '';
            $nameAr = $item->name['ar'] ?? '';
            $displayName = $nameEn ?: $nameAr;

            // For subcategories, show parent category
            $parentInfo = '';
            if ($type === 'subcategory' && $item->category) {
                $catName = $item->category->name['en'] ?? $item->category->name['ar'] ?? '';
                $parentInfo = " [Category: {$catName}]";
            }

            // Current item header
            $this->renderCurrentItem($current, $total, $displayName, $item->id, $parentInfo, $type);

            try {
                if ($type === 'category') {
                    $success = $this->dalleService->generateForCategory($item);
                } else {
                    $success = $this->dalleService->generateForSubcategory($item);
                }

                if ($success) {
                    $progress['completed'][] = ['id' => $item->id, 'name' => $displayName];
                    $this->line("  <fg=green>  ✓ SUCCESS</> — Image generated for \"{$displayName}\"");
                } else {
                    $error = $this->dalleService->getLastError() ?: 'Unknown error';
                    $progress['errors'][] = ['id' => $item->id, 'name' => $displayName, 'error' => $error];
                    $this->line("  <fg=red>  ✗ FAILED</> — \"{$displayName}\": {$error}");
                }
            } catch (\Exception $e) {
                $progress['errors'][] = ['id' => $item->id, 'name' => $displayName, 'error' => $e->getMessage()];
                $this->line("  <fg=red>  ✗ ERROR</> — \"{$displayName}\": {$e->getMessage()}");
            }

            $progress['last_id'] = $item->id;
            $this->saveProgress($progressFile, $progress);

            // Show running stats
            $completed = count($progress['completed']);
            $errors = count($progress['errors']);
            $this->line("  <fg=gray>  Stats: {$completed} done, {$errors} failed, " . ($total - $current) . " remaining</>");

            // Wait between images (skip after last item)
            if ($index < $total - 1) {
                if ($isAuto) {
                    $this->info("  Waiting 65s for rate limit...");
                    sleep(65);
                } else {
                    $this->renderCountdown(65);
                }
            }

            $this->newLine();
        }

        // Section summary
        $this->renderSectionSummary($type, $progress);

        return Command::SUCCESS;
    }

    protected function renderHeader(string $title): void
    {
        $line = str_repeat('═', strlen($title) + 4);
        $this->newLine();
        $this->line("<fg=cyan>╔{$line}╗</>");
        $this->line("<fg=cyan>║</>  <fg=white;options=bold>{$title}</>  <fg=cyan>║</>");
        $this->line("<fg=cyan>╚{$line}╝</>");
    }

    protected function renderSectionHeader(string $title): void
    {
        $line = str_repeat('─', 50);
        $this->line("<fg=yellow>┌{$line}┐</>");
        $this->line("<fg=yellow>│</> <fg=white;options=bold>{$title}</>" . str_repeat(' ', max(0, 49 - mb_strlen($title))) . "<fg=yellow>│</>");
        $this->line("<fg=yellow>└{$line}┘</>");
    }

    protected function renderItemsTable(string $type, $items): void
    {
        $headers = $type === 'category'
            ? ['#', 'ID', 'Name (EN)', 'Name (AR)']
            : ['#', 'ID', 'Name (EN)', 'Name (AR)', 'Parent Category'];

        $rows = [];
        foreach ($items as $index => $item) {
            $row = [
                $index + 1,
                $item->id,
                $item->name['en'] ?? '—',
                $item->name['ar'] ?? '—',
            ];

            if ($type === 'subcategory') {
                $row[] = $item->category
                    ? ($item->category->name['en'] ?? $item->category->name['ar'] ?? '—')
                    : '—';
            }

            $rows[] = $row;
        }

        $this->table($headers, $rows);
    }

    protected function renderCurrentItem(int $current, int $total, string $name, int $id, string $parentInfo, string $type): void
    {
        $percent = round(($current / $total) * 100);
        $barWidth = 30;
        $filled = (int) round(($percent / 100) * $barWidth);
        $empty = $barWidth - $filled;
        $bar = str_repeat('█', $filled) . str_repeat('░', $empty);

        $icon = $type === 'category' ? '📁' : '📄';

        $this->line("  <fg=cyan>┌──────────────────────────────────────────────────┐</>");
        $this->line("  <fg=cyan>│</> {$icon} <fg=white;options=bold>[{$current}/{$total}]</> <fg=yellow>{$name}</> <fg=gray>(ID: {$id})</>{$parentInfo}");
        $this->line("  <fg=cyan>│</> <fg=green>{$bar}</> {$percent}%");
        $this->line("  <fg=cyan>└──────────────────────────────────────────────────┘</>");
    }

    protected function renderCountdown(int $seconds): void
    {
        $this->newLine();
        for ($i = $seconds; $i > 0; $i--) {
            $mins = intdiv($i, 60);
            $secs = $i % 60;
            $timeStr = $mins > 0 ? sprintf('%dm %02ds', $mins, $secs) : "{$secs}s";

            // Overwrite same line
            $this->output->write("\r  <fg=gray>  ⏳ Next image in:</> <fg=yellow;options=bold>{$timeStr}</> <fg=gray>" . str_repeat('·', min($i, 30)) . "</>   ");
            sleep(1);
        }
        // Clear the countdown line
        $this->output->write("\r" . str_repeat(' ', 80) . "\r");
    }

    protected function renderSectionSummary(string $type, array $progress): void
    {
        $completed = count($progress['completed']);
        $errors = count($progress['errors']);
        $icon = $type === 'category' ? '📁' : '📂';

        $this->line("<fg=cyan>  ═══════════════════════════════════════</>");
        $this->line("  {$icon} <fg=white;options=bold>" . strtoupper($type) . " SUMMARY</>");
        $this->line("  <fg=green>  ✓ Completed:</> {$completed}");
        $this->line("  <fg=red>  ✗ Errors:</>    {$errors}");

        if (!empty($progress['errors'])) {
            $this->newLine();
            $this->line("  <fg=red;options=bold>  Failed items:</>");
            foreach ($progress['errors'] as $err) {
                $this->line("  <fg=red>    • {$err['name']} (ID: {$err['id']}): {$err['error']}</>");
            }
        }

        $this->line("<fg=cyan>  ═══════════════════════════════════════</>");
    }

    protected function renderFinalSummary(): void
    {
        // Load both progress files for final overview
        $catProgress = $this->loadProgress('ai_generate_progress_category.json');
        $subProgress = $this->loadProgress('ai_generate_progress_subcategory.json');

        $this->line("<fg=cyan;options=bold>╔══════════════════════════════════════════╗</>");
        $this->line("<fg=cyan;options=bold>║</>  <fg=white;options=bold>FINAL OVERVIEW</>                          <fg=cyan;options=bold>║</>");
        $this->line("<fg=cyan;options=bold>╠══════════════════════════════════════════╣</>");

        if ($catProgress) {
            $cc = count($catProgress['completed'] ?? []);
            $ce = count($catProgress['errors'] ?? []);
            $this->line("<fg=cyan;options=bold>║</>  📁 Categories:   <fg=green>{$cc} done</>  <fg=red>{$ce} failed</>      <fg=cyan;options=bold>║</>");
        }

        if ($subProgress) {
            $sc = count($subProgress['completed'] ?? []);
            $se = count($subProgress['errors'] ?? []);
            $this->line("<fg=cyan;options=bold>║</>  📂 Subcategories: <fg=green>{$sc} done</>  <fg=red>{$se} failed</>      <fg=cyan;options=bold>║</>");
        }

        $this->line("<fg=cyan;options=bold>╚══════════════════════════════════════════╝</>");
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
