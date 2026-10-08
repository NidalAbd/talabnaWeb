<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shop pages (2026-10-08): the shop becomes a real storefront: its own link (talbna.cloud/shop/{slug}), a cover,
 * contact buttons, a place on the map, opening hours by day (open/closed now), an offer, featured products, and daily
 * visit/contact counts for the owner's insights.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (! Schema::hasColumn('shops', 'slug')) {
                $table->string('slug', 90)->nullable()->unique()->after('name');
                $table->string('cover')->nullable()->after('logo');
                $table->string('phone', 30)->nullable();
                $table->string('whatsapp', 30)->nullable();
                $table->string('address', 200)->nullable();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->unsignedBigInteger('country_id')->nullable()->index();
                $table->unsignedBigInteger('city_id')->nullable()->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                // [{"d":0..6 (0 = Sunday), "open":"09:00", "close":"22:00"}]; a day not listed is closed.
                $table->json('week_hours')->nullable();
                $table->string('offer', 160)->nullable();
                $table->date('offer_until')->nullable();
                $table->json('featured_post_ids')->nullable();
            }
        });

        if (! Schema::hasTable('shop_daily_stats')) {
            Schema::create('shop_daily_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('shop_user_id');
                $table->date('day');
                $table->unsignedInteger('visits')->default(0);
                $table->unsignedInteger('contacts')->default(0);
                $table->unique(['shop_user_id', 'day']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_daily_stats');
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['slug', 'cover', 'phone', 'whatsapp', 'address', 'lat', 'lng', 'country_id', 'city_id',
                'category_id', 'week_hours', 'offer', 'offer_until', 'featured_post_ids']);
        });
    }
};
