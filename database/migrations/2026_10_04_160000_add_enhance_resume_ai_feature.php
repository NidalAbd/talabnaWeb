<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** AI resume helper (2026-10-04): price editable in Admin -> Pricing. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_features')->insertOrIgnore([
            'key' => 'enhance_resume', 'points_cost' => 2, 'enabled' => true,
            'description' => 'Improve the resume headline and summary, suggest skills',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('ai_features')->where('key', 'enhance_resume')->delete();
    }
};
