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
        Schema::create('business_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('budget_title');
            $table->text('budget_description')->nullable();
            $table->decimal('total_budget', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('budget_period', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('category'); // overall, marketing, development, operations, etc.
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('spent_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_budgets');
    }
}; 