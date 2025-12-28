<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('exchange_rate_to_usd', 12, 6)->default(1.000000)->after('price_per_point');
            $table->boolean('use_custom_price')->default(false)->after('exchange_rate_to_usd');
        });

        // Set default exchange rates for common countries
        $exchangeRates = [
            'AE' => 3.6725,    // UAE Dirham
            'SA' => 3.75,      // Saudi Riyal
            'EG' => 50.85,     // Egyptian Pound
            'JO' => 0.709,     // Jordanian Dinar
            'KW' => 0.308,     // Kuwaiti Dinar
            'BH' => 0.376,     // Bahraini Dinar
            'OM' => 0.385,     // Omani Rial
            'QA' => 3.64,      // Qatari Riyal
            'LB' => 89500,     // Lebanese Pound
            'SY' => 13000,     // Syrian Pound
            'IQ' => 1310,      // Iraqi Dinar
            'PS' => 3.65,      // Palestinian (uses ILS)
            'IL' => 3.65,      // Israeli Shekel
            'TR' => 35.20,     // Turkish Lira
            'MA' => 10.05,     // Moroccan Dirham
            'TN' => 3.15,      // Tunisian Dinar
            'DZ' => 135.50,    // Algerian Dinar
            'LY' => 4.85,      // Libyan Dinar
            'SD' => 601,       // Sudanese Pound
            'YE' => 250,       // Yemeni Rial
            'US' => 1.00,      // US Dollar
            'GB' => 0.79,      // British Pound
            'EU' => 0.92,      // Euro (for European countries)
            'DE' => 0.92,      // Germany (Euro)
            'FR' => 0.92,      // France (Euro)
            'IN' => 83.50,     // Indian Rupee
            'PK' => 278.50,    // Pakistani Rupee
            'BD' => 110,       // Bangladeshi Taka
        ];

        foreach ($exchangeRates as $countryCode => $rate) {
            DB::table('countries')
                ->where('country_code', $countryCode)
                ->update(['exchange_rate_to_usd' => $rate]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate_to_usd', 'use_custom_price']);
        });
    }
};
