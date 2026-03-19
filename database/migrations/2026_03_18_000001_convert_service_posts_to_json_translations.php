<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add temporary JSON columns
        Schema::table('service_posts', function (Blueprint $table) {
            $table->json('title_json')->nullable()->after('title');
            $table->json('description_json')->nullable()->after('description');
        });

        // Step 2: Migrate existing data - wrap current values in JSON with 'ar' as default locale
        // (Most existing content is in Arabic based on the app's default)
        DB::statement("
            UPDATE service_posts
            SET title_json = JSON_OBJECT('ar', COALESCE(title, '')),
                description_json = JSON_OBJECT('ar', COALESCE(description, ''))
            WHERE title IS NOT NULL OR description IS NOT NULL
        ");

        // Step 3: Drop old columns and rename new ones
        Schema::table('service_posts', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });

        Schema::table('service_posts', function (Blueprint $table) {
            $table->renameColumn('title_json', 'title');
            $table->renameColumn('description_json', 'description');
        });
    }

    public function down(): void
    {
        // Step 1: Add temporary string columns
        Schema::table('service_posts', function (Blueprint $table) {
            $table->string('title_str', 255)->nullable()->after('title');
            $table->text('description_str')->nullable()->after('description');
        });

        // Step 2: Extract 'ar' value back to string
        DB::statement("
            UPDATE service_posts
            SET title_str = JSON_UNQUOTE(JSON_EXTRACT(title, '$.ar')),
                description_str = JSON_UNQUOTE(JSON_EXTRACT(description, '$.ar'))
            WHERE title IS NOT NULL
        ");

        // Step 3: Drop JSON columns and rename
        Schema::table('service_posts', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });

        Schema::table('service_posts', function (Blueprint $table) {
            $table->renameColumn('title_str', 'title');
            $table->renameColumn('description_str', 'description');
        });
    }
};
