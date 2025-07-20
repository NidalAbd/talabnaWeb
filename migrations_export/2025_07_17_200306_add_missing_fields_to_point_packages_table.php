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
        Schema::table('point_packages', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('validity_days');
            $table->string('icon', 100)->nullable()->after('discount_percentage');
            $table->string('color', 7)->nullable()->after('icon');
            $table->integer('max_purchases')->default(0)->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_packages', function (Blueprint $table) {
            $table->dropColumn(['discount_percentage', 'icon', 'color', 'max_purchases']);
        });
    }
};
