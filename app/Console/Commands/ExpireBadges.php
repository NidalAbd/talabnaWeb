<?php

namespace App\Console\Commands;

use App\Models\BadgeType;
use App\Models\Notification;
use App\Models\ServicePost;
use App\Services\BadgeService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireBadges extends Command
{
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
                $oldBadgeName = $post->badgeType?->name_ar ?? $post->have_badge;

                // Reset to default badge
                $post->badge_type_id = $defaultBadge?->id;
                $post->have_badge = $defaultBadge?->name_ar ?? 'عادي';
                $post->badge_duration = 0;
                $post->badge_expires_at = null;
                $post->save();

                // Create expiration notification
                $this->createExpirationNotification($post, $oldBadgeName);

                $count++;
                $this->info("Expired badge for service post #{$post->id} - Badge was: {$oldBadgeName}");
                Log::info("Expired badge for service post #{$post->id} - Badge was: {$oldBadgeName}");
            }

            // Fallback: Find posts using old have_badge system
            $expiredByOldSystem = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
                ->whereNull('badge_type_id')
                ->whereNotNull('badge_expires_at')
                ->where('badge_expires_at', '<', Carbon::now())
                ->get();

            foreach ($expiredByOldSystem as $post) {
                $oldBadge = $post->have_badge;

                // Reset badge to standard
                $post->badge_type_id = $defaultBadge?->id;
                $post->have_badge = 'عادي';
                $post->badge_duration = 0;
                $post->badge_expires_at = null;
                $post->save();

                // Create expiration notification
                $this->createExpirationNotification($post, $oldBadge);

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
                    $oldBadgeName = $post->badgeType?->name_ar ?? $post->have_badge;

                    // Reset badge to standard
                    $post->badge_type_id = $defaultBadge?->id;
                    $post->have_badge = $defaultBadge?->name_ar ?? 'عادي';
                    $post->badge_duration = 0;
                    $post->badge_expires_at = null;
                    $post->save();

                    // Create expiration notification
                    $this->createExpirationNotification($post, $oldBadgeName);

                    $count++;
                    $this->info("Expired badge for service post #{$post->id} using fallback method - Badge was: {$oldBadgeName}");
                    Log::info("Expired badge for service post #{$post->id} using fallback method - Badge was: {$oldBadgeName}");
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

            return 0;
        } catch (\Exception $e) {
            $this->error("An error occurred during badge expiration: " . $e->getMessage());
            Log::error("Badge expiration error: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Create expiration notification
     */
    protected function createExpirationNotification(ServicePost $post, string $oldBadgeName): void
    {
        $message = json_encode([
            'ar' => "تم تغيير شارة منشور الخدمة من {$oldBadgeName} إلى عادي بسبب انتهاء المدة.",
            'en' => "Service Post Badge changed from {$oldBadgeName} to normal due to expiration."
        ]);

        Notification::create([
            'message' => $message,
            'user_id' => $post->user_id,
            'type' => 'badge'
        ]);
    }
}
