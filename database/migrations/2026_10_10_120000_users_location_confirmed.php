<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * When the user confirmed their country and city (2026-10-10). New accounts used to get the first country (Palestine,
 * Gaza) whatever their real place; until confirmed, the app asks once with a suggestion from the phone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'location_confirmed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('location_confirmed_at')->nullable();
            });
        }
        // Accounts whose place was clearly chosen by a person: a verified phone, a country change, or a country
        // other than the old default. The rest (no country, or the default) are asked once.
        $default = DB::table('countries')->orderBy('id')->value('id');
        DB::table('users')->whereNull('location_confirmed_at')->whereNotNull('country_id')
            ->where(function ($q) use ($default) {
                $q->whereNotNull('phone_verified_at')->orWhereNotNull('country_changed_at')->orWhere('country_id', '!=', $default);
            })
            ->update(['location_confirmed_at' => now()]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'location_confirmed_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('location_confirmed_at'));
        }
    }
};
