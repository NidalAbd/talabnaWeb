<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plan changes and top-up packs (2026-10-04).
 *  - upgrade: pay the new price minus the unused part of the current period; starts now
 *  - downgrade: scheduled_plan_id, starts when the current period ends
 *  - top-up packs: extra uses of a plan allowance until the current period ends (extras)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('user_subscriptions', 'extras')) {
                $table->json('extras')->nullable()->after('usage');
            }
            if (! Schema::hasColumn('user_subscriptions', 'scheduled_plan_id')) {
                $table->unsignedBigInteger('scheduled_plan_id')->nullable()->after('auto_renew');
            }
            if (! Schema::hasColumn('user_subscriptions', 'replaced_by_id')) {
                $table->unsignedBigInteger('replaced_by_id')->nullable()->after('scheduled_plan_id');
            }
        });

        if (! Schema::hasTable('subscription_addons')) {
            Schema::create('subscription_addons', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->json('name');
                $table->string('feature_key', 64); // plan feature it adds to, e.g. ai_images_per_month
                $table->unsignedInteger('amount');
                $table->unsignedInteger('price_points');
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            $now = now();
            DB::table('subscription_addons')->insert([
                ['slug' => 'ai_images_5', 'name' => json_encode(['en' => '+5 AI images', 'ar' => '+5 صور بالذكاء الاصطناعي']),
                    'feature_key' => 'ai_images_per_month', 'amount' => 5, 'price_points' => 8, 'is_active' => true,
                    'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['slug' => 'featured_posts_3', 'name' => json_encode(['en' => '+3 featured posts', 'ar' => '+3 منشورات مميزة']),
                    'feature_key' => 'featured_posts', 'amount' => 3, 'price_points' => 10, 'is_active' => true,
                    'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Cancelling used to end access at once although the app promised "access continues until …".
        // Cancelled now only means "don't renew"; give back access to those still inside their period.
        DB::table('user_subscriptions')->where('status', 'cancelled')->where('expires_at', '>', now())
            ->update(['status' => 'active', 'auto_renew' => false]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_addons');
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['extras', 'scheduled_plan_id', 'replaced_by_id']);
        });
    }
};
