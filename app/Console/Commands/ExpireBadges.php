<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\ServicePost;
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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting badge expiration check...');
        Log::info('Badge expiration check started');

        try {
            // Find posts with badge_expires_at in the past
            $expiredByExpirationDate = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
                ->whereNotNull('badge_expires_at')
                ->where('badge_expires_at', '<', Carbon::now())
                ->get();

            $count = $expiredByExpirationDate->count();

            foreach ($expiredByExpirationDate as $post) {
                $oldBadge = $post->have_badge;

                // Reset badge to standard
                $post->have_badge = 'عادي';
                $post->badge_duration = 0;
                $post->badge_expires_at = null;
                $post->save();

                // Create expiration notification
                $message = json_encode([
                    'ar' => "تم تغيير شارة منشور الخدمة من {$oldBadge} إلى عادي بسبب انتهاء المدة.",
                    'en' => "Service Post Badge changed from {$oldBadge} to normal due to expiration."
                ]);

                Notification::create([
                    'message' => $message,
                    'user_id' => $post->user_id,
                    'type' => 'badge'
                ]);

                $this->info("Expired badge for service post #{$post->id} - Badge was: {$oldBadge}");
                Log::info("Expired badge for service post #{$post->id} - Badge was: {$oldBadge}");
            }

            // Fallback: check posts without badge_expires_at but with duration
            $potentiallyExpired = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
                ->where('badge_duration', '>', 0)
                ->whereNull('badge_expires_at')
                ->get();

            foreach ($potentiallyExpired as $post) {
                $expirationDate = Carbon::parse($post->created_at)->addDays($post->badge_duration);

                if (Carbon::now()->greaterThanOrEqualTo($expirationDate)) {
                    $oldBadge = $post->have_badge;

                    // Reset badge to standard
                    $post->have_badge = 'عادي';
                    $post->badge_duration = 0;
                    $post->badge_expires_at = null;
                    $post->save();

                    // Create expiration notification
                    $message = json_encode([
                        'ar' => "تم تغيير شارة منشور الخدمة من {$oldBadge} إلى عادي بسبب انتهاء المدة.",
                        'en' => "Service Post Badge changed from {$oldBadge} to normal due to expiration."
                    ]);

                    Notification::create([
                        'message' => $message,
                        'user_id' => $post->user_id,
                        'type' => 'badge'
                    ]);

                    $count++;
                    $this->info("Expired badge for service post #{$post->id} using fallback method - Badge was: {$oldBadge}");
                    Log::info("Expired badge for service post #{$post->id} using fallback method - Badge was: {$oldBadge}");
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
}
