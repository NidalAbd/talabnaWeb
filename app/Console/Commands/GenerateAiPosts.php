<?php

namespace App\Console\Commands;

use App\Models\Categories;
use App\Models\Photos;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\User;
use App\Services\DalleImageService;
use App\Services\OpenAiContentService;
use App\Traits\LogsCommandExecution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateAiPosts extends Command
{
    use LogsCommandExecution;

    protected $signature = 'ai:posts
        {--category= : Category ID (required)}
        {--subcategory= : Specific subcategory ID (optional)}
        {--count=3 : Total posts to generate}
        {--photos=1 : Photos per post (1-3)}
        {--bot-user= : Bot user ID (required)}
        {--random : Distribute posts across random subcategories}
        {--auto : Non-interactive for cron}';

    protected $description = 'Generate AI service posts with GPT-4 text content and DALL-E realistic photos';

    protected OpenAiContentService $contentService;
    protected DalleImageService $dalleService;

    public function __construct(OpenAiContentService $contentService, DalleImageService $dalleService)
    {
        parent::__construct();
        $this->contentService = $contentService;
        $this->dalleService = $dalleService;
    }

    public function handle(): int
    {
        $categoryId = $this->option('category');
        $subcategoryId = $this->option('subcategory');
        $count = (int) $this->option('count');
        $photosCount = min(3, max(1, (int) $this->option('photos')));
        $botUserId = $this->option('bot-user');
        $isAuto = $this->option('auto');
        $isRandom = $this->option('random');

        // Validate required options
        if (!$categoryId) {
            $this->error('--category is required. Specify a category ID.');
            return Command::FAILURE;
        }

        if (!$botUserId) {
            $this->error('--bot-user is required. Specify a bot user ID.');
            return Command::FAILURE;
        }

        // Validate bot user exists
        $botUser = User::find($botUserId);
        if (!$botUser) {
            $this->error("Bot user with ID {$botUserId} not found.");
            return Command::FAILURE;
        }

        // Validate category exists
        $category = Categories::find($categoryId);
        if (!$category) {
            $this->error("Category with ID {$categoryId} not found.");
            return Command::FAILURE;
        }

        $categoryName = $category->name['en'] ?? $category->name['ar'] ?? "Category {$categoryId}";

        // Get subcategories
        $query = Sub_categories::with('category')->where('categories_id', $categoryId);
        if ($subcategoryId) {
            $query->where('id', $subcategoryId);
        }
        $subcategories = $query->orderBy('id', 'asc')->get();

        if ($subcategories->isEmpty()) {
            $this->error('No subcategories found for the given criteria.');
            return Command::FAILURE;
        }

        // Build the post generation plan
        // If --random or specific subcategory: total = $count posts
        // If no subcategory and no --random: $count posts per subcategory (legacy)
        $postPlan = []; // Array of ['subcategory' => Sub_categories, 'post_num' => int]

        if ($subcategoryId || $isRandom) {
            // Total $count posts, distributed across subcategories
            for ($i = 0; $i < $count; $i++) {
                $sub = $subcategoryId
                    ? $subcategories->first()
                    : $subcategories->random();
                $postPlan[] = ['subcategory' => $sub, 'post_num' => $i + 1];
            }
        } else {
            // Legacy: $count posts per each subcategory
            foreach ($subcategories as $sub) {
                for ($i = 0; $i < $count; $i++) {
                    $postPlan[] = ['subcategory' => $sub, 'post_num' => $i + 1];
                }
            }
        }

        $totalPosts = count($postPlan);

        // Progress file
        $progressFile = "ai_generate_progress_posts_cat{$categoryId}.json";
        $progress = $this->loadProgress($progressFile);

        if (!$isAuto) {
            $choice = $this->choice(
                'Start from beginning or continue from last progress?',
                ['start', 'continue'],
                $progress ? 'continue' : 'start'
            );
        } else {
            $choice = $progress ? 'continue' : 'start';
        }

        $this->logStart(['category_id' => $categoryId, 'total_posts' => $totalPosts]);

        if ($choice === 'start') {
            $progress = [
                'status' => 'running',
                'completed_posts' => 0,
                'total_posts' => $totalPosts,
                'total_photos_per_post' => $photosCount,
                'errors' => [],
                'last_post_global_index' => 0,
                'current_item' => null,
                'started_at' => now()->toISOString(),
            ];
            $this->saveProgress($progressFile, $progress);
        }

        $progress = array_merge([
            'status' => 'running',
            'completed_posts' => 0,
            'total_posts' => $totalPosts,
            'total_photos_per_post' => $photosCount,
            'errors' => [],
            'last_post_global_index' => 0,
            'current_item' => null,
            'started_at' => now()->toISOString(),
        ], $progress ?? []);

        // Mark as running
        $progress['status'] = 'running';
        $progress['total_posts'] = $totalPosts;
        $this->saveProgress($progressFile, $progress);

        // Header
        $this->renderHeader("AI Post Generator — {$categoryName}");
        $this->info("  Bot User: {$botUser->name} (ID: {$botUserId})");
        $mode = $isRandom ? 'Random subcategories' : ($subcategoryId ? 'Specific subcategory' : 'All subcategories');
        $this->info("  Mode: {$mode} | Total posts: {$totalPosts} | Photos each: {$photosCount}");
        $this->newLine();

        $startIndex = $progress['last_post_global_index'] ?? 0;

        for ($idx = $startIndex; $idx < $totalPosts; $idx++) {
            $plan = $postPlan[$idx];
            $subcategory = $plan['subcategory'];
            $subcategoryName = $subcategory->name['en'] ?? $subcategory->name['ar'] ?? "Subcategory {$subcategory->id}";
            $postNum = $idx + 1;

            $this->line("  <fg=cyan>┌──────────────────────────────────────────────────┐</>");
            $this->line("  <fg=cyan>│</> 📝 <fg=white;options=bold>Post {$postNum}/{$totalPosts}</> for <fg=yellow>{$subcategoryName}</>");
            $this->line("  <fg=cyan>└──────────────────────────────────────────────────┘</>");

            try {
                // Update current item in progress
                $progress['current_item'] = "Generating text for {$subcategoryName} (post {$postNum}/{$totalPosts})";
                $this->saveProgress($progressFile, $progress);

                // Step 1: Generate text content with GPT
                $this->line("  <fg=gray>  → Generating text content with GPT...</>");
                $content = $this->contentService->generatePostContent($categoryName, $subcategoryName, 'عرض');

                $this->line("  <fg=green>  ✓ Title:</> {$content['title']['en']}");
                $this->line("  <fg=green>  ✓ Price:</> \${$content['price']}");

                // Step 2: Create ServicePost record
                $post = ServicePost::create([
                    'user_id' => $botUserId,
                    'categories_id' => $categoryId,
                    'sub_categories_id' => $subcategory->id,
                    'title' => json_encode($content['title']),
                    'description' => json_encode($content['description']),
                    'price' => $content['price'],
                    'type' => 'عرض',
                    'state' => 'published',
                    'country_id' => $botUser->country_id ?? 1,
                    'city_id' => $botUser->city_id ?? 1,
                ]);

                $this->line("  <fg=green>  ✓ Post created</> (ID: {$post->id})");

                // Step 3: Generate photos
                for ($p = 0; $p < $photosCount; $p++) {
                    $photoNum = $p + 1;

                    $progress['current_item'] = "Generating photo {$photoNum}/{$photosCount} for \"{$content['title']['en']}\"";
                    $this->saveProgress($progressFile, $progress);

                    $this->line("  <fg=gray>  → Generating photo {$photoNum}/{$photosCount}...</>");

                    $imagePrompt = $this->contentService->generateImagePrompt($categoryName, $subcategoryName, $content['title']['en']);
                    $imagePath = $this->dalleService->generatePostPhoto($imagePrompt);

                    if ($imagePath) {
                        $photo = new Photos(['src' => $imagePath]);
                        $post->photos()->save($photo);
                        $this->line("  <fg=green>  ✓ Photo {$photoNum} saved</>");
                    } else {
                        $error = $this->dalleService->getLastError() ?: 'Unknown error';
                        $this->line("  <fg=red>  ✗ Photo {$photoNum} failed:</> {$error}");
                        $progress['errors'][] = ['post_id' => $post->id, 'photo' => $photoNum, 'error' => $error];
                    }

                    // Wait between DALL-E calls (skip after last photo of last post)
                    $isLastPhoto = ($p === $photosCount - 1);
                    $isLastPost = ($idx === $totalPosts - 1);
                    if (!($isLastPhoto && $isLastPost)) {
                        if ($isAuto) {
                            $this->info("  Waiting 65s for rate limit...");
                            sleep(65);
                        } else {
                            $this->renderCountdown(65);
                        }
                    }
                }

                $progress['completed_posts']++;

            } catch (\Exception $e) {
                $this->line("  <fg=red>  ✗ ERROR:</> {$e->getMessage()}");
                $progress['errors'][] = [
                    'subcategory_id' => $subcategory->id,
                    'post_index' => $idx,
                    'error' => $e->getMessage(),
                ];
            }

            // Save progress after each post
            $progress['last_post_global_index'] = $idx + 1;
            $this->saveProgress($progressFile, $progress);

            // Running stats
            $completed = $progress['completed_posts'];
            $errors = count($progress['errors']);
            $this->line("  <fg=gray>  Stats: {$completed}/{$totalPosts} posts done, {$errors} errors</>");
            $this->newLine();
        }

        // Mark as finished
        $progress['status'] = 'finished';
        $progress['current_item'] = null;
        $progress['finished_at'] = now()->toISOString();
        $this->saveProgress($progressFile, $progress);

        $this->logFinish($progress['completed_posts']);

        // Final summary
        $this->renderFinalSummary($progress);

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

    protected function renderCountdown(int $seconds): void
    {
        $this->newLine();
        for ($i = $seconds; $i > 0; $i--) {
            $mins = intdiv($i, 60);
            $secs = $i % 60;
            $timeStr = $mins > 0 ? sprintf('%dm %02ds', $mins, $secs) : "{$secs}s";

            $this->output->write("\r  <fg=gray>  ⏳ Next image in:</> <fg=yellow;options=bold>{$timeStr}</> <fg=gray>" . str_repeat('·', min($i, 30)) . "</>   ");
            sleep(1);
        }
        $this->output->write("\r" . str_repeat(' ', 80) . "\r");
    }

    protected function renderFinalSummary(array $progress): void
    {
        $completed = $progress['completed_posts'];
        $errors = count($progress['errors']);

        $this->line("<fg=cyan;options=bold>╔══════════════════════════════════════════╗</>");
        $this->line("<fg=cyan;options=bold>║</>  <fg=white;options=bold>POST GENERATION SUMMARY</>                 <fg=cyan;options=bold>║</>");
        $this->line("<fg=cyan;options=bold>╠══════════════════════════════════════════╣</>");
        $this->line("<fg=cyan;options=bold>║</>  📝 Posts created: <fg=green>{$completed}</>                     <fg=cyan;options=bold>║</>");
        $this->line("<fg=cyan;options=bold>║</>  ❌ Errors:        <fg=red>{$errors}</>                     <fg=cyan;options=bold>║</>");
        $this->line("<fg=cyan;options=bold>╚══════════════════════════════════════════╝</>");

        if (!empty($progress['errors'])) {
            $this->newLine();
            $this->line("  <fg=red;options=bold>  Failed items:</>");
            foreach ($progress['errors'] as $err) {
                $this->line("  <fg=red>    • {$err['error']}</>");
            }
        }
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
