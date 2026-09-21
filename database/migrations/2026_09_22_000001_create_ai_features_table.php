<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_features')) {
            return;
        }

        // What each AI action costs in points. The SERVER charges from this table (the app only
        // displays the price), so a price can be changed here without an app update.
        Schema::create('ai_features', function (Blueprint $table) {
            $table->string('key', 40)->primary();
            $table->unsignedInteger('points_cost');
            $table->boolean('enabled')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        foreach ([
            // key, points, enabled, description
            ['enhance_title', 1, true, 'Improve a post title'],
            ['enhance_description', 1, true, 'Improve a post description'],
            ['translate_text', 1, false, 'Translate a title or description into another language'],
            ['suggest_category', 1, false, 'Suggest the category and subcategory from the text or photos'],
            ['suggest_price', 1, false, 'Suggest a price'],
            ['generate_image', 3, false, 'Generate an image for the post'],
            ['generate_video', 20, false, 'Generate a short video for the post'],
        ] as [$key, $points, $enabled, $description]) {
            DB::table('ai_features')->insert([
                'key' => $key, 'points_cost' => $points, 'enabled' => $enabled,
                'description' => $description, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_features');
    }
};
