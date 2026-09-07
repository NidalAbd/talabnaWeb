<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds an explicit `is_job_category` flag so job-specific features
     * (resume matching, job post fields) can check this instead of relying
     * on the hardcoded `categories_id == 1` convention already used
     * elsewhere in the app (e.g. categories_tab.dart's "default to Jobs"
     * comment) — safer across environments where seed IDs may differ.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_job_category')->default(false)->after('is_popular');
        });

        // Flag the existing Jobs category (id 1 by the app's existing
        // convention) so the flag is usable immediately after migrating.
        DB::table('categories')->where('id', 1)->update(['is_job_category' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('is_job_category');
        });
    }
};
