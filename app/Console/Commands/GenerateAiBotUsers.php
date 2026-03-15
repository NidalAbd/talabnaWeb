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
        {--users=10 : Number of bot users to create}
        {--posts-per-user=5 : Posts per user}
        {--photos=1 : Photos per post (1-3)}
        {--country= : Specific country ID (optional, otherwise all countries)}
        {--auto : Non-interactive}';

    protected $description = 'Generate bot users with random AI posts across categories for each country';

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
        $usersCount = (int) $this->option('users');
        $postsPerUser = (int) $this->option('posts-per-user');
        $photosCount = min(3, max(1, (int) $this->option('photos')));
        $countryId = $this->option('country');
        $isAuto = $this->option('auto');

        // Get countries
        $countriesQuery = countries::query();
        if ($countryId) {
            $countriesQuery->where('id', $countryId);
        }
        $allCountries = $countriesQuery->get();

        if ($allCountries->isEmpty()) {
            $this->error('No countries found.');
            return Command::FAILURE;
        }

        // Get all categories with subcategories
        $categories = Categories::has('sub_categories')->with('sub_categories')->get();
        if ($categories->isEmpty()) {
            $this->error('No categories with subcategories found.');
            return Command::FAILURE;
        }

        $usersPerCountry = $countryId ? $usersCount : max(1, intdiv($usersCount, $allCountries->count()));
        $totalUsers = $countryId ? $usersCount : $usersPerCountry * $allCountries->count();
        $totalPosts = $totalUsers * $postsPerUser;

        $progressFile = 'ai_seed_progress.json';
        $progress = [
            'status' => 'running',
            'total_users' => $totalUsers,
            'total_posts' => $totalPosts,
            'created_users' => 0,
            'created_posts' => 0,
            'errors' => [],
            'current_item' => null,
            'started_at' => now()->toISOString(),
        ];
        $this->saveProgress($progressFile, $progress);

        $this->info("Generating {$totalUsers} users with {$postsPerUser} posts each = {$totalPosts} total posts");
        $this->info("Countries: {$allCountries->count()} | Users per country: {$usersPerCountry}");
        $this->newLine();

        foreach ($allCountries as $country) {
            $countryName = $country->name['en'] ?? $country->name['ar'] ?? "Country {$country->id}";
            $countryCities = cities::where('country_id', $country->id)->get();

            $this->info("=== {$countryName} ({$usersPerCountry} users) ===");

            for ($u = 0; $u < $usersPerCountry; $u++) {
                $city = $countryCities->isNotEmpty() ? $countryCities->random() : null;

                // Create bot user via DB::table to avoid model issues
                $userName = 'bot_' . Str::random(8);
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

                // Generate profile photo
                try {
                    $gender = $user->gender === 'ذكر' ? 'male' : 'female';
                    $photoPrompt = "Professional profile photo of a {$gender} person, natural look, plain background, headshot portrait, photorealistic";
                    $imagePath = $this->dalleService->generatePostPhoto($photoPrompt);
                    if ($imagePath) {
                        $photo = new Photos(['src' => $imagePath]);
                        $user->photos()->save($photo);
                    }
                } catch (\Exception $e) {
                    $this->line("    Profile photo failed: {$e->getMessage()}");
                }

                $progress['created_users']++;
                $progress['current_item'] = "User: {$user->name} ({$countryName})";
                $this->saveProgress($progressFile, $progress);

                $this->line("  User #{$progress['created_users']}: {$user->name} (ID: {$user->id})");

                // Create posts for this user
                for ($p = 0; $p < $postsPerUser; $p++) {
                    try {
                        $category = $categories->random();
                        $subcategory = $category->sub_categories->random();
                        $categoryName = $category->name['en'] ?? $category->name['ar'] ?? '';
                        $subcategoryName = $subcategory->name['en'] ?? $subcategory->name['ar'] ?? '';

                        $postNum = $p + 1;
                        $progress['current_item'] = "Post {$postNum}/{$postsPerUser} for {$user->name}: {$subcategoryName}";
                        $this->saveProgress($progressFile, $progress);

                        // Generate content
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

                        // Generate photo
                        for ($ph = 0; $ph < $photosCount; $ph++) {
                            try {
                                $imagePrompt = $this->contentService->generateImagePrompt($categoryName, $subcategoryName, $content['title']['en']);
                                $imagePath = $this->dalleService->generatePostPhoto($imagePrompt);

                                if ($imagePath) {
                                    $photo = new Photos(['src' => $imagePath]);
                                    $post->photos()->save($photo);
                                }
                            } catch (\Exception $e) {
                                $progress['errors'][] = "Photo failed for post {$post->id}: {$e->getMessage()}";
                            }

                            // Rate limit
                            if (!($p === $postsPerUser - 1 && $ph === $photosCount - 1)) {
                                sleep(65);
                            }
                        }

                        $progress['created_posts']++;
                        $this->line("    Post {$postNum}: {$content['title']['en']} (ID: {$post->id})");

                    } catch (\Exception $e) {
                        $this->error("    Post {$postNum} failed: {$e->getMessage()}");
                        $progress['errors'][] = "Post failed for user {$user->id}: {$e->getMessage()}";
                    }

                    $this->saveProgress($progressFile, $progress);
                }
            }
        }

        $progress['status'] = 'finished';
        $progress['finished_at'] = now()->toISOString();
        $this->saveProgress($progressFile, $progress);

        $this->newLine();
        $this->info("Done! Created {$progress['created_users']} users and {$progress['created_posts']} posts.");
        if (!empty($progress['errors'])) {
            $this->warn(count($progress['errors']) . " errors occurred.");
        }

        return Command::SUCCESS;
    }

    private function generateArabicName(): string
    {
        $firstNames = ['أحمد', 'محمد', 'خالد', 'سارة', 'فاطمة', 'عمر', 'ياسمين', 'نور', 'علي', 'مريم', 'حسن', 'ليلى', 'كريم', 'دينا', 'طارق', 'هبة', 'ماجد', 'رنا', 'سامي', 'لينا'];
        $lastNames = ['الحسن', 'العمري', 'الخطيب', 'النجار', 'الشيخ', 'المصري', 'الأحمد', 'الحاج', 'البدوي', 'السعيد', 'الفلسطيني', 'القدس', 'الشامي', 'العراقي', 'الأردني'];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    protected function saveProgress(string $file, array $progress): void
    {
        Storage::disk('local')->put($file, json_encode($progress, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
