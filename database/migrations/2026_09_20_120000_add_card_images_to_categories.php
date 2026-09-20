<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Card-shaped images for the app's Categories screen: a wide hero image
     * (`banner_src`, 3:1) and a landscape tile image (`tile_src`, ~1.55:1).
     * They are separate columns — NOT extra rows in `photos` — because
     * already-installed app versions read `photos[0]` as "the category image"
     * everywhere; adding rows there would put a wide banner where they draw a
     * square avatar. Older apps simply ignore these two fields, and newer ones
     * fall back to `photos[0]` while a category has none.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('banner_src')->nullable()->after('is_job_category');
            $table->string('tile_src')->nullable()->after('banner_src');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['banner_src', 'tile_src']);
        });
    }
};
