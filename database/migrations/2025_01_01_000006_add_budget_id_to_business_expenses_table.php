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
        Schema::table('business_expenses', function (Blueprint $table) {
            $table->foreignId('budget_id')->nullable()->constrained('business_budgets')->onDelete('set null')->after('investment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_expenses', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropColumn('budget_id');
        });
    }
}; 