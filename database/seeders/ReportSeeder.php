<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\User;
use App\Models\ServicePost;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users and posts to report
        $users = User::take(5)->get();
        $posts = ServicePost::take(5)->get();

        if ($users->isEmpty() || $posts->isEmpty()) {
            $this->command->info('No users or posts found. Please run UserSeeder and ServicePostSeeder first.');
            return;
        }

        $reasons = [
            'Spam',
            'inappropriate content',
            'Harassment',
            'false information'
        ];

        // Create reports for users (some users will have multiple reports)
        foreach ($users as $index => $user) {
            $reportCount = rand(1, 8); // Some users will have more reports
            
            for ($i = 0; $i < $reportCount; $i++) {
                Report::create([
                    'reportable_type' => User::class,
                    'reportable_id' => $user->id,
                    'user_id' => $users->random()->id,
                    'reason' => $reasons[array_rand($reasons)],
                    'created_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                ]);
            }
        }

        // Create reports for posts (some posts will have multiple reports)
        foreach ($posts as $index => $post) {
            $reportCount = rand(1, 6); // Some posts will have more reports
            
            for ($i = 0; $i < $reportCount; $i++) {
                Report::create([
                    'reportable_type' => ServicePost::class,
                    'reportable_id' => $post->id,
                    'user_id' => $users->random()->id,
                    'reason' => $reasons[array_rand($reasons)],
                    'created_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                ]);
            }
        }

        // Create some recent reports (today and this week)
        for ($i = 0; $i < 10; $i++) {
            $reportable = rand(0, 1) ? $users->random() : $posts->random();
            
            Report::create([
                'reportable_type' => get_class($reportable),
                'reportable_id' => $reportable->id,
                'user_id' => $users->random()->id,
                'reason' => $reasons[array_rand($reasons)],
                'created_at' => Carbon::now()->subHours(rand(0, 24)),
                'updated_at' => Carbon::now()->subHours(rand(0, 24)),
            ]);
        }

        $this->command->info('Sample reports created successfully!');
        $this->command->info('Total reports created: ' . Report::count());
    }
}
