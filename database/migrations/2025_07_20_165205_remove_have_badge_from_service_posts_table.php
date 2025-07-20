<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing data from have_badge to level_id
        $this->migrateBadgeData();
        
        // Then remove the have_badge column
        Schema::table('service_posts', function (Blueprint $table) {
            $table->dropColumn('have_badge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_posts', function (Blueprint $table) {
            $table->enum('have_badge', ['عادي', 'ذهبي', 'ماسي'])->default('عادي')->after('type');
        });
        
        // Migrate data back from level_id to have_badge
        $this->migrateLevelDataBack();
    }

    /**
     * Migrate existing badge data to level_id
     */
    private function migrateBadgeData(): void
    {
        // Get or create levels
        $regularLevel = DB::table('levels')->where('name->ar', 'عادي')->first();
        $goldLevel = DB::table('levels')->where('name->ar', 'ذهبي')->first();
        $diamondLevel = DB::table('levels')->where('name->ar', 'ماسي')->first();

        // Create levels if they don't exist
        if (!$regularLevel) {
            $regularLevelId = DB::table('levels')->insertGetId([
                'name' => json_encode(['ar' => 'عادي', 'en' => 'Regular']),
                'description' => json_encode(['ar' => 'المستوى العادي', 'en' => 'Regular level']),
                'icon' => 'circle',
                'color' => '#6c757d',
                'points_per_day' => 0,
                'view_boost_percentage' => 0,
                'display_order' => 0,
                'is_active' => true,
                'is_premium' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $regularLevelId = $regularLevel->id;
        }

        if (!$goldLevel) {
            $goldLevelId = DB::table('levels')->insertGetId([
                'name' => json_encode(['ar' => 'ذهبي', 'en' => 'Gold']),
                'description' => json_encode(['ar' => 'المستوى الذهبي', 'en' => 'Gold level']),
                'icon' => 'star',
                'color' => '#ffc107',
                'points_per_day' => 10,
                'view_boost_percentage' => 50,
                'display_order' => 1,
                'is_active' => true,
                'is_premium' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $goldLevelId = $goldLevel->id;
        }

        if (!$diamondLevel) {
            $diamondLevelId = DB::table('levels')->insertGetId([
                'name' => json_encode(['ar' => 'ماسي', 'en' => 'Diamond']),
                'description' => json_encode(['ar' => 'المستوى الماسي', 'en' => 'Diamond level']),
                'icon' => 'gem',
                'color' => '#17a2b8',
                'points_per_day' => 20,
                'view_boost_percentage' => 100,
                'display_order' => 2,
                'is_active' => true,
                'is_premium' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $diamondLevelId = $diamondLevel->id;
        }

        // Update service posts based on their have_badge value
        DB::table('service_posts')
            ->where('have_badge', 'عادي')
            ->update(['level_id' => $regularLevelId]);

        DB::table('service_posts')
            ->where('have_badge', 'ذهبي')
            ->update(['level_id' => $goldLevelId]);

        DB::table('service_posts')
            ->where('have_badge', 'ماسي')
            ->update(['level_id' => $diamondLevelId]);

        // Set level_id to 0 for posts that don't have a level (fallback)
        DB::table('service_posts')
            ->whereNull('level_id')
            ->update(['level_id' => 0]);
    }

    /**
     * Migrate level data back to have_badge (for rollback)
     */
    private function migrateLevelDataBack(): void
    {
        // Get level mappings
        $regularLevel = DB::table('levels')->where('name->ar', 'عادي')->first();
        $goldLevel = DB::table('levels')->where('name->ar', 'ذهبي')->first();
        $diamondLevel = DB::table('levels')->where('name->ar', 'ماسي')->first();

        if ($regularLevel) {
            DB::table('service_posts')
                ->where('level_id', $regularLevel->id)
                ->update(['have_badge' => 'عادي']);
        }

        if ($goldLevel) {
            DB::table('service_posts')
                ->where('level_id', $goldLevel->id)
                ->update(['have_badge' => 'ذهبي']);
        }

        if ($diamondLevel) {
            DB::table('service_posts')
                ->where('level_id', $diamondLevel->id)
                ->update(['have_badge' => 'ماسي']);
        }

        // Set default for any remaining posts
        DB::table('service_posts')
            ->whereNull('have_badge')
            ->update(['have_badge' => 'عادي']);
    }
};
