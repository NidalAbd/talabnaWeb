<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\ServicePost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class UpdateServicePostsBadgeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:service-post-badge-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the badge status of service posts every 24 hours from their creation time.';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $servicePosts = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
            ->where('created_at', '<=', now()->subHours(24))
            ->get();
        foreach ($servicePosts as $post) {
            $newDuration = $post->badge_duration - 1;
            if ($newDuration <= 0) {
                $post->have_badge = 'عادي';
                $message = "Service Post Badge changed to normal due to duration times up.";
                $notification = new Notification([
                    'message' => $message,
                    'user_id' => $post->user_id,
                    'type'    => 'badge'
                ]);
                $notification->save();
            }
            $post->badge_duration = $newDuration;
            $post->save();
        }

        $this->info('Service post badge status updated successfully.');
    }
}
