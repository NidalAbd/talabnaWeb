<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Release A (2026-10-07):
 *  - price types: fixed, negotiable, free, on_request, none (requests, services), salary (jobs, a from–to range)
 *  - post lifecycle: posts expire after N days (renew in one tap), can be marked reserved or sold. Expired and sold posts
 *    leave the `published` state, so every public list (feed, search, reels, categories, sitemap...) hides them without
 *    touching those queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE service_posts MODIFY state ENUM('published','archive','not published','rejected','expired','sold') NOT NULL DEFAULT 'published'");
        }

        Schema::table('service_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('service_posts', 'price_type')) {
                $table->string('price_type', 16)->default('fixed')->after('price');
            }
            if (! Schema::hasColumn('service_posts', 'price_max')) {
                $table->decimal('price_max', 15, 2)->nullable()->after('price_type');
            }
            if (! Schema::hasColumn('service_posts', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->index();
            }
            if (! Schema::hasColumn('service_posts', 'expiry_reminded_at')) {
                $table->timestamp('expiry_reminded_at')->nullable();
            }
            if (! Schema::hasColumn('service_posts', 'reserved_at')) {
                $table->timestamp('reserved_at')->nullable();
            }
            if (! Schema::hasColumn('service_posts', 'sold_at')) {
                $table->timestamp('sold_at')->nullable();
            }
        });

        // Existing posts get a full period from today, so nothing disappears the day this ships.
        $days = (int) (DB::table('app_settings')->where('key', 'posts.expiry_days')->value('value') ?? 30) ?: 30;
        DB::table('service_posts')->where('state', 'published')->whereNull('expires_at')
            ->update(['expires_at' => now()->addDays($days)]);

        // Posts that were saved without a price (0) on job and request categories read better as "no price".
        DB::table('service_posts')->where('price', 0)->where('price_type', 'fixed')->update(['price_type' => 'none']);

        // Pro and Business renew their posts automatically.
        foreach (['pro', 'business'] as $slug) {
            $plan = DB::table('subscription_plans')->where('slug', $slug)->first();
            if (! $plan) {
                continue;
            }
            $features = json_decode($plan->features ?? '{}', true) ?: [];
            $features['auto_renew_posts'] = true;
            DB::table('subscription_plans')->where('id', $plan->id)->update(['features' => json_encode($features)]);
        }
    }

    public function down(): void
    {
        Schema::table('service_posts', function (Blueprint $table) {
            foreach (['price_type', 'price_max', 'expires_at', 'expiry_reminded_at', 'reserved_at', 'sold_at'] as $c) {
                if (Schema::hasColumn('service_posts', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
