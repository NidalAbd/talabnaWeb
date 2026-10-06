<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Release C: per-category details (year, mileage, rooms, area, condition...). */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_posts', 'details')) {
            Schema::table('service_posts', fn (Blueprint $t) => $t->json('details')->nullable());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_posts', 'details')) {
            Schema::table('service_posts', fn (Blueprint $t) => $t->dropColumn('details'));
        }
    }
};
