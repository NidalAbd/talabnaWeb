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
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('price_per_point', 10, 2)->default(7.50)->after('currency_name');
            $table->string('currency_symbol', 10)->nullable()->after('price_per_point');
            $table->boolean('allow_point_transfers')->default(true)->after('currency_symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['price_per_point', 'currency_symbol', 'allow_point_transfers']);
        });
    }
};
