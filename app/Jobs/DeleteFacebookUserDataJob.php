<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\ServicePost;
use App\Models\Favorite;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;

class DeleteFacebookUserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $facebookUserId;
    protected $confirmationCode;

    /**
     * Create a new job instance.
     *
     * @param string $facebookUserId
     * @param string $confirmationCode
     * @return void
     */
    public function __construct($facebookUserId, $confirmationCode)
    {
        $this->facebookUserId = $facebookUserId;
        $this->confirmationCode = $confirmationCode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Find users connected to this Facebook ID
        $users = User::where('facebook_id', $this->facebookUserId)->get();

        foreach ($users as $user) {
            DB::beginTransaction();
            try {
                // Delete or anonymize all user data

                // 1. Delete service posts
                ServicePost::where('user_id', $user->id)->delete();

                // 2. Delete favorites
                Favorite::where('user_id', $user->id)->delete();

                // 3. Delete comments
                Comment::where('user_id', $user->id)->delete();

                // 4. Remove Facebook connection from user account
                // If the user only uses Facebook login, you might want to delete the account completely
                // Otherwise, just remove the Facebook connection
                $user->facebook_id = null;
                $user->save();

                // You could also choose to completely delete the user:
                // $user->delete();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                // Log the error
                Log::error('Facebook data deletion failed: ' . $e->getMessage());

                // You may want to retry the job
                $this->release(3600); // Retry after 1 hour
                return;
            }
        }

        // Mark deletion as complete
        Cache::put('fb_deletion_' . $this->confirmationCode, true, now()->addDays(90));
    }
}
