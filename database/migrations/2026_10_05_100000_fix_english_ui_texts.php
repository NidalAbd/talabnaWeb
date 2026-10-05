<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** English UI texts found wrong during the 2026-10-05 QA run (only exact old values are changed). */
return new class extends Migration
{
    private array $fixes = [
        ['settings', 'change_theme', 'change Theme', 'Change Theme'],
        ['help', 'frequently_questions', 'Frequently Questions', 'Frequently Asked Questions'],
    ];

    public function up(): void
    {
        foreach ($this->fixes as [$group, $key, $old, $new]) {
            DB::table('translations')->where(['locale' => 'en', 'group' => $group, 'key' => $key, 'value' => $old])
                ->update(['value' => $new, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach ($this->fixes as [$group, $key, $old, $new]) {
            DB::table('translations')->where(['locale' => 'en', 'group' => $group, 'key' => $key, 'value' => $new])
                ->update(['value' => $old, 'updated_at' => now()]);
        }
    }
};
