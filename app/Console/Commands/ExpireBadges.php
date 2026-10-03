<?php

namespace App\Console\Commands;

use App\Models\BadgeType;
use App\Models\Notification;
use App\Models\ServicePost;
use App\Models\User;
use App\Notifications\BadgeExpirationNotification;
use App\Services\BadgeService;
use App\Traits\LogsCommandExecution;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireBadges extends Command
{
    use LogsCommandExecution;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and expire badges that have reached their expiration time';

    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        parent::__construct();
        $this->badgeService = $badgeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting badge expiration check...');
        Log::info('Badge expiration check started');
        $this->logStart();

        try {
            $defaultBadge = BadgeType::getDefault();
            $count = 0;

            // Find posts with badge_expires_at in the past (new system with badge_type_id)
            $expiredByNewSystem = ServicePost::whereNotNull('badge_type_id')
                ->where('badge_type_id', '!=', $defaultBadge?->id ?? 0)
                ->whereNotNull('badge_expires_at')
                ->where('badge_expires_at', '<', Carbon::now())
                ->get();

            foreach ($expiredByNewSystem as $post) {
                $oldBadgeName = $this->badgeNames($post);

                // Reset to default badge
                $post->badge_type_id = $defaultBadge?->id;
                $post->have_badge = $defaultBadge?->name_ar ?? 'عادي';
                $post->badge_duration = 0;
                $post->badge_expires_at = null;
                $post->save();

                // Create expiration notification
                $this->createExpirationNotification($post, $oldBadgeName);

                $count++;
                $this->info("Expired badge for service post #{$post->id} - Badge was: " . ($oldBadgeName['en'] ?? reset($oldBadgeName)) . "");
                Log::info("Expired badge for service post #{$post->id} - Badge was: " . ($oldBadgeName['en'] ?? reset($oldBadgeName)) . "");
            }

            // Fallback: Find posts using old have_badge system
            $expiredByOldSystem = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
                ->whereNull('badge_type_id')
                ->whereNotNull('badge_expires_at')
                ->where('badge_expires_at', '<', Carbon::now())
                ->get();

            foreach ($expiredByOldSystem as $post) {
                $oldBadge = $post->have_badge;
                $oldNames = $this->badgeNames($post);

                // Reset badge to standard
                $post->badge_type_id = $defaultBadge?->id;
                $post->have_badge = 'عادي';
                $post->badge_duration = 0;
                $post->badge_expires_at = null;
                $post->save();

                // Create expiration notification
                $this->createExpirationNotification($post, $oldNames);

                $count++;
                $this->info("Expired badge for service post #{$post->id} (legacy) - Badge was: {$oldBadge}");
                Log::info("Expired badge for service post #{$post->id} (legacy) - Badge was: {$oldBadge}");
            }

            // Fallback: check posts without badge_expires_at but with duration
            $potentiallyExpired = ServicePost::where(function ($query) use ($defaultBadge) {
                    $query->whereIn('have_badge', ['ذهبي', 'ماسي'])
                        ->orWhere(function ($q) use ($defaultBadge) {
                            $q->whereNotNull('badge_type_id')
                                ->where('badge_type_id', '!=', $defaultBadge?->id ?? 0);
                        });
                })
                ->where('badge_duration', '>', 0)
                ->whereNull('badge_expires_at')
                ->get();

            foreach ($potentiallyExpired as $post) {
                $expirationDate = Carbon::parse($post->created_at)->addDays($post->badge_duration);

                if (Carbon::now()->greaterThanOrEqualTo($expirationDate)) {
                    $oldBadgeName = $this->badgeNames($post);

                    // Reset badge to standard
                    $post->badge_type_id = $defaultBadge?->id;
                    $post->have_badge = $defaultBadge?->name_ar ?? 'عادي';
                    $post->badge_duration = 0;
                    $post->badge_expires_at = null;
                    $post->save();

                    // Create expiration notification
                    $this->createExpirationNotification($post, $oldBadgeName);

                    $count++;
                    $this->info("Expired badge for service post #{$post->id} using fallback method - Badge was: " . ($oldBadgeName['en'] ?? reset($oldBadgeName)) . "");
                    Log::info("Expired badge for service post #{$post->id} using fallback method - Badge was: " . ($oldBadgeName['en'] ?? reset($oldBadgeName)) . "");
                } else {
                    // Update the badge_expires_at field for future checks
                    $post->badge_expires_at = $expirationDate;
                    $post->save();

                    $this->info("Updated missing badge_expires_at for post #{$post->id} to {$expirationDate}");
                    Log::info("Updated missing badge_expires_at for post #{$post->id} to {$expirationDate}");
                }
            }

            $this->info("Badge expiration completed. Expired {$count} badges.");
            Log::info("Badge expiration completed. Expired {$count} badges.");
            $this->logFinish($count);

            return 0;
        } catch (\Exception $e) {
            $this->error("An error occurred during badge expiration: " . $e->getMessage());
            Log::error("Badge expiration error: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->logError($e->getMessage());

            return 1;
        }
    }

    /**
     * Create expiration notification
     */
    /** The expired badge's name in every language (legacy posts: matched by their Arabic name). */
    protected function badgeNames(ServicePost $post): array
    {
        $names = $post->badgeType?->name;
        if (is_array($names) && $names) return $names;
        $legacy = (string) $post->have_badge;
        $match = \App\Models\BadgeType::all()->first(fn ($b) => in_array($legacy, (array) $b->name, true));
        return $match ? (array) $match->name : ['ar' => $legacy, 'en' => $legacy];
    }

    protected function normalNames(): array
    {
        return (array) (\App\Models\BadgeType::where('slug', 'normal')->first()?->name ?? ['ar' => 'عادي', 'en' => 'Normal']);
    }

    protected function createExpirationNotification(ServicePost $post, array $oldBadgeName): void
    {
        $normal = $this->normalNames();
        // In-app notification list: one text per supported language.
        $texts = [];
        foreach (array_keys(BadgeExpirationNotification::TEXT) as $lang) {
            $texts[$lang] = BadgeExpirationNotification::render($lang, $oldBadgeName, $normal, $post->id)[1];
        }
        $message = json_encode($texts, JSON_UNESCAPED_UNICODE);

        Notification::create([
            'message' => $message,
            'user_id' => $post->user_id,
            'type' => 'badge'
        ]);

        // Send Firebase push notification
        try {
            $user = User::find($post->user_id);
            if ($user && !empty($user->fcm_token)) {
                $user->notify(new BadgeExpirationNotification($oldBadgeName, $post->id, $normal));
                $this->info("FCM notification sent to user #{$post->user_id}");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to send FCM for badge expiration on post #{$post->id}: " . $e->getMessage());
        }
    }
}
