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
            // Find posts with level_id > 0 and badge_expires_at in the past
            $expiredByExpirationDate = ServicePost::where('level_id', '>', 0)
                ->whereNotNull('badge_expires_at')
                ->where('badge_expires_at', '<', Carbon::now())
                ->get();

            $count = $expiredByExpirationDate->count();

            foreach ($expiredByExpirationDate as $post) {
                $oldLevel = $post->level ? $post->level->localized_name : 'Premium';
                
                // Reset to regular level (level_id = 0 or null)
                $post->level_id = 0;
                $post->badge_duration = 0;
                $post->badge_expires_at = null;
                $post->save();

                // Create expiration notification
                Notification::create([
                    'user_id' => $post->user_id,
                    'title' => [
                        'ar' => "انتهت صلاحية مستوى المنشور",
                        'en' => "Service Post Level Expired"
                    ],
                    'body' => [
                        'ar' => "تم تغيير مستوى المنشور من {$oldLevel} إلى عادي بسبب انتهاء الصلاحية.",
                        'en' => "Service Post Level changed from {$oldLevel} to regular due to expiration."
                    ],
                    'type' => 'level_expiration',
                    'data' => [
                        'service_post_id' => $post->id,
                        'old_level' => $oldLevel,
                        'new_level' => 'Regular'
                    ]
                ]);

                $this->info("Expired level for service post #{$post->id} - Level was: {$oldLevel}");
                Log::info("Expired level for service post #{$post->id} - Level was: {$oldLevel}");
            }

            // Fallback: check posts without badge_expires_at but with duration
            $potentiallyExpired = ServicePost::where('level_id', '>', 0)
                ->where('badge_duration', '>', 0)
                ->whereNull('badge_expires_at')
                ->get();

            foreach ($potentiallyExpired as $post) {
                $expirationDate = Carbon::parse($post->created_at)->addDays($post->badge_duration);

                if (Carbon::now()->greaterThanOrEqualTo($expirationDate)) {
                    $oldLevel = $post->level ? $post->level->localized_name : 'Premium';
                    
                    // Reset to regular level
                    $post->level_id = 0;
                    $post->badge_duration = 0;
                    $post->badge_expires_at = null;
                    $post->save();

                    // Create expiration notification
                    Notification::create([
                        'user_id' => $post->user_id,
                        'title' => [
                            'ar' => "انتهت صلاحية مستوى المنشور",
                            'en' => "Service Post Level Expired"
                        ],
                        'body' => [
                            'ar' => "تم تغيير مستوى المنشور من {$oldLevel} إلى عادي بسبب انتهاء الصلاحية.",
                            'en' => "Service Post Level changed from {$oldLevel} to regular due to expiration."
                        ],
                        'type' => 'level_expiration',
                        'data' => [
                            'service_post_id' => $post->id,
                            'old_level' => $oldLevel,
                            'new_level' => 'Regular'
                        ]
                    ]);

                    $this->info("Expired level for service post #{$post->id} using fallback method - Level was: {$oldLevel}");
                    Log::info("Expired level for service post #{$post->id} using fallback method - Level was: {$oldLevel}");
                } else {
                    // Update the badge_expires_at field for future checks
                    $post->badge_expires_at = $expirationDate;
                    $post->save();

                    $this->info("Updated missing badge_expires_at for post #{$post->id} to {$expirationDate}");
                    Log::info("Updated missing badge_expires_at for post #{$post->id} to {$expirationDate}");
                }
            }

            $this->info("Level expiration completed. Expired {$count} levels.");
            Log::info("Level expiration completed. Expired {$count} levels.");

        } catch (\Exception $e) {
            $this->error("An error occurred during level expiration: " . $e->getMessage());
            Log::error("Level expiration error: " . $e->getMessage());
        }
    }
}
