<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_code', 2)->nullable()->after('country_code');
        });

        // Set ISO codes for existing countries
        $isoCodes = [
            1 => 'PS',  // Palestine
            2 => 'EG',  // Egypt
            3 => 'SA',  // Saudi Arabia
            4 => 'AE',  // UAE
            5 => 'IQ',  // Iraq
            6 => 'JO',  // Jordan
            7 => 'BH',  // Bahrain
            8 => 'YE',  // Yemen
            9 => 'QA',  // Qatar
            10 => 'DZ', // Algeria
            11 => 'MA', // Morocco
            12 => 'TN', // Tunisia
            13 => 'LY', // Libya
            14 => 'SY', // Syria
            15 => 'SD', // Sudan
            16 => 'KW', // Kuwait
            17 => 'LB', // Lebanon
            18 => 'OM', // Oman
            19 => 'MR', // Mauritania
            20 => 'SO', // Somalia
            21 => 'DJ', // Djibouti
            22 => 'KM', // Comoros
        ];

        foreach ($isoCodes as $id => $code) {
            DB::table('countries')->where('id', $id)->update(['iso_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('iso_code');
        });
    }
};
