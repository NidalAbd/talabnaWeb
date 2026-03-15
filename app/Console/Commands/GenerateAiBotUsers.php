<?php

namespace App\Console\Commands;

use App\Models\Categories;
use App\Models\Photos;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\User;
use App\Models\countries;
use App\Models\cities;
use App\Services\DalleImageService;
use App\Services\OpenAiContentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateAiBotUsers extends Command
{
    protected $signature = 'ai:seed
        {--users=10 : Total bot users to create}
        {--posts-per-user=2 : Posts per user}
        {--photos=1 : Photos per post (1-3)}
        {--country= : Specific country ID (optional)}
        {--batch=3 : Users per batch run (0 = no limit)}
        {--auto : Non-interactive}';

    protected $description = 'Generate bot users with random AI posts. Supports batching for cron.';

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
        $targetUsers = (int) $this->option('users');
        $postsPerUser = (int) $this->option('posts-per-user');
        $photosCount = min(3, max(1, (int) $this->option('photos')));
        $countryId = $this->option('country');
        $batchSize = (int) $this->option('batch');

        $progressFile = 'ai_seed_progress.json';

        // Load or create progress
        $progress = $this->loadProgress($progressFile);

        if ($progress && $progress['status'] === 'finished') {
            // Previous run finished — start fresh
            $progress = null;
        }

        if (!$progress) {
            $progress = [
                'status' => 'running',
                'target_users' => $targetUsers,
                'posts_per_user' => $postsPerUser,
                'photos_per_post' => $photosCount,
                'country_id' => $countryId,
                'total_users' => $targetUsers,
                'total_posts' => $targetUsers * $postsPerUser,
                'created_users' => 0,
                'created_posts' => 0,
                'errors' => [],
                'current_item' => null,
                'started_at' => now()->toISOString(),
            ];
        }

        // Resume values
        $progress['status'] = 'running';
        $this->saveProgress($progressFile, $progress);

        // Get countries
        $countriesQuery = countries::query();
        if ($progress['country_id']) {
            $countriesQuery->where('id', $progress['country_id']);
        }
        $allCountries = $countriesQuery->get();

        if ($allCountries->isEmpty()) {
            $this->error('No countries found.');
            $progress['status'] = 'error';
            $this->saveProgress($progressFile, $progress);
            return Command::FAILURE;
        }

        $categories = Categories::has('sub_categories')->with('sub_categories')->get();
        if ($categories->isEmpty()) {
            $this->error('No categories with subcategories found.');
            $progress['status'] = 'error';
            $this->saveProgress($progressFile, $progress);
            return Command::FAILURE;
        }

        $remaining = $progress['target_users'] - $progress['created_users'];
        $thisBatch = $batchSize > 0 ? min($batchSize, $remaining) : $remaining;

        if ($remaining <= 0) {
            $progress['status'] = 'finished';
            $progress['finished_at'] = now()->toISOString();
            $this->saveProgress($progressFile, $progress);
            $this->info('All users already created!');
            return Command::SUCCESS;
        }

        $this->info("Batch: creating {$thisBatch} users ({$progress['created_users']}/{$progress['target_users']} done)");

        // Round-robin: distribute users evenly across countries
        $countryCount = $allCountries->count();

        for ($u = 0; $u < $thisBatch; $u++) {
            // Pick country based on total created so far (round-robin)
            $countryIndex = $progress['created_users'] % $countryCount;
            $country = $allCountries->values()[$countryIndex];
            $countryCities = cities::where('country_id', $country->id)->get();
            $city = $countryCities->isNotEmpty() ? $countryCities->random() : null;
            $countryName = $country->name['en'] ?? $country->name['ar'] ?? '';

            // Create user
            $userName = 'bot_' . Str::random(8);
            try {
                $userId = DB::table('users')->insertGetId([
                    'name' => $this->generateArabicName(),
                    'user_name' => $userName,
                    'email' => $userName . '@bot.talabna.com',
                    'password' => Hash::make(Str::random(16)),
                    'gender' => collect(['ذكر', 'انثى'])->random(),
                    'country_id' => $country->id,
                    'city_id' => $city?->id,
                    'is_active' => 'active',
                    'date_of_birth' => now()->subYears(rand(20, 45))->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $user = User::find($userId);
            } catch (\Exception $e) {
                $this->error("User creation failed: {$e->getMessage()}");
                $progress['errors'][] = "User: {$e->getMessage()}";
                $this->saveProgress($progressFile, $progress);
                continue;
            }

            $progress['created_users']++;
            $progress['current_item'] = "User: {$user->name} ({$countryName})";
            $this->saveProgress($progressFile, $progress);
            $this->line("  [{$progress['created_users']}/{$progress['target_users']}] {$user->name} - {$countryName}");

            // Generate profile photo
            try {
                $gender = $user->gender === 'ذكر' ? 'male' : 'female';
                $photoPrompt = "Professional profile photo of a {$gender} person, natural look, plain background, headshot portrait, photorealistic";
                $imagePath = $this->dalleService->generatePostPhoto($photoPrompt);
                if ($imagePath) {
                    $photo = new Photos(['src' => $imagePath]);
                    $user->photos()->save($photo);
                    $this->line("    Profile photo saved");
                }
                sleep(15); // Rate limit
            } catch (\Exception $e) {
                $this->line("    Profile photo failed: {$e->getMessage()}");
            }

            // Create posts
            $postPhotos = collect([1, 2])->random(); // Random 1 or 2 photos
            for ($p = 0; $p < $postsPerUser; $p++) {
                $postNum = $p + 1;
                try {
                    $category = $categories->random();
                    $subcategory = $category->sub_categories->random();
                    $categoryName = $category->name['en'] ?? $category->name['ar'] ?? '';
                    $subcategoryName = $subcategory->name['en'] ?? $subcategory->name['ar'] ?? '';

                    $progress['current_item'] = "Post {$postNum}/{$postsPerUser} for {$user->name}";
                    $this->saveProgress($progressFile, $progress);

                    $content = $this->contentService->generatePostContent($categoryName, $subcategoryName, 'عرض');

                    $post = ServicePost::create([
                        'user_id' => $user->id,
                        'categories_id' => $category->id,
                        'sub_categories_id' => $subcategory->id,
                        'title' => $content['title']['ar'] ?? $content['title']['en'] ?? '',
                        'description' => $content['description']['ar'] ?? $content['description']['en'] ?? '',
                        'price' => $content['price'],
                        'type' => 'عرض',
                        'state' => 'published',
                        'country_id' => $country->id,
                        'city_id' => $city?->id ?? 1,
                    ]);

                    // Generate post photos
                    for ($ph = 0; $ph < $postPhotos; $ph++) {
                        try {
                            $imagePrompt = $this->contentService->generateImagePrompt($categoryName, $subcategoryName, $content['title']['en'] ?? '');
                            $imagePath = $this->dalleService->generatePostPhoto($imagePrompt);
                            if ($imagePath) {
                                $photo = new Photos(['src' => $imagePath]);
                                $post->photos()->save($photo);
                            }
                            sleep(15); // Rate limit
                        } catch (\Exception $e) {
                            $progress['errors'][] = "Photo for post {$post->id}: {$e->getMessage()}";
                        }
                    }

                    $progress['created_posts']++;
                    $postTitle = $content['title']['ar'] ?? $content['title']['en'] ?? '';
                    $this->line("    Post {$postNum}: {$postTitle} (ID: {$post->id})");

                } catch (\Exception $e) {
                    $this->error("    Post {$postNum} failed: {$e->getMessage()}");
                    $progress['errors'][] = "Post for user {$user->id}: {$e->getMessage()}";
                }
                $this->saveProgress($progressFile, $progress);
            }
        }

        // Check if all done
        if ($progress['created_users'] >= $progress['target_users']) {
            $progress['status'] = 'finished';
            $progress['finished_at'] = now()->toISOString();
            $this->info("All done! {$progress['created_users']} users, {$progress['created_posts']} posts.");
        } else {
            $progress['status'] = 'paused';
            $progress['current_item'] = "Waiting for next batch...";
            $remaining = $progress['target_users'] - $progress['created_users'];
            $this->info("Batch done. {$remaining} users remaining. Run again to continue.");
        }

        $this->saveProgress($progressFile, $progress);
        return Command::SUCCESS;
    }

    private function generateArabicName(): string
    {
        $firstNames = ['أحمد', 'محمد', 'خالد', 'سارة', 'فاطمة', 'عمر', 'ياسمين', 'نور', 'علي', 'مريم', 'حسن', 'ليلى', 'كريم', 'دينا', 'طارق', 'هبة', 'ماجد', 'رنا', 'سامي', 'لينا'];
        $lastNames = ['الحسن', 'العمري', 'الخطيب', 'النجار', 'الشيخ', 'المصري', 'الأحمد', 'الحاج', 'البدوي', 'السعيد', 'الفلسطيني', 'القدس', 'الشامي', 'العراقي', 'الأردني'];
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    protected function loadProgress(string $file): ?array
    {
        if (Storage::disk('local')->exists($file)) {
            return json_decode(Storage::disk('local')->get($file), true);
        }
        return null;
    }

    protected function saveProgress(string $file, array $progress): void
    {
        Storage::disk('local')->put($file, json_encode($progress, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
