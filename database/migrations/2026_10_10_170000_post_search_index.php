<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Search that stays fast with many posts (2026-10-10): one indexed row per post and detail (filters and option
 * counts), and a plain-text copy of each post's words with a FULLTEXT index (search used LIKE '%...%' over the JSON
 * title/description, which reads every post). Filled by `php artisan posts:index-search`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('post_attribute_values')) {
            Schema::create('post_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_post_id');
                $table->unsignedBigInteger('category_id');
                $table->string('key', 32);
                $table->string('str', 100)->nullable();
                $table->bigInteger('num')->nullable();
                $table->index('service_post_id');
                $table->index(['category_id', 'key', 'str']);
                $table->index(['category_id', 'key', 'num']);
            });
        }
        if (! Schema::hasColumn('service_posts', 'search_text')) {
            Schema::table('service_posts', function (Blueprint $table) {
                $table->mediumText('search_text')->nullable();
            });
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE service_posts ADD FULLTEXT INDEX service_posts_search_text_ft (search_text)');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_attribute_values');
        if (Schema::hasColumn('service_posts', 'search_text')) {
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE service_posts DROP INDEX service_posts_search_text_ft');
            }
            Schema::table('service_posts', fn (Blueprint $table) => $table->dropColumn('search_text'));
        }
    }
};
