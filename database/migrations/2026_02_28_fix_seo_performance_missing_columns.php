<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seo_performance', function (Blueprint $table) {
            if (!Schema::hasColumn('seo_performance', 'trend_change')) {
                $table->decimal('trend_change', 8, 2)->default(0)->after('trend');
            }
            if (!Schema::hasColumn('seo_performance', 'devices')) {
                $table->json('devices')->nullable()->after('countries');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_performance', function (Blueprint $table) {
            if (Schema::hasColumn('seo_performance', 'trend_change')) {
                $table->dropColumn('trend_change');
            }
            if (Schema::hasColumn('seo_performance', 'devices')) {
                $table->dropColumn('devices');
            }
        });
    }
};
