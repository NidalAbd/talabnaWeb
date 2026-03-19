<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Subscription Plans (admin-defined)
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->json('name');                          // {"ar": "...", "en": "..."}
            $table->json('description')->nullable();       // {"ar": "...", "en": "..."}
            $table->string('slug')->unique();              // e.g. "basic", "pro", "business"
            $table->integer('price_points');                // cost in points per month
            $table->integer('duration_days')->default(30); // subscription duration
            $table->json('features');                      // JSON features config
            // features example:
            // {
            //   "ai_images_per_month": 10,
            //   "bonus_points_percent": 5,
            //   "featured_posts": 3,
            //   "auto_translate_posts": true,
            //   "priority_support": false,
            //   "max_photos_per_post": 20,
            //   "badge_discount_percent": 10
            // }
            $table->string('color', 20)->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });

        // User Subscriptions (active/expired)
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->integer('points_paid');
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->json('features_snapshot');             // snapshot of features at purchase time
            $table->json('usage')->nullable();             // track usage: {"ai_images_used": 3, ...}
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['expires_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
