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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('investor_name');
            $table->string('investor_email');
            $table->decimal('investment_amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('investment_type', ['equity', 'loan', 'grant'])->default('equity');
            $table->date('investment_date');
            $table->decimal('expected_return', 15, 2)->nullable();
            $table->decimal('return_percentage', 5, 2)->nullable();
            $table->enum('status', ['active', 'completed', 'pending'])->default('active');
            $table->string('purpose')->nullable(); // license, advertising, salary, development, etc.
            $table->text('notes')->nullable();
            $table->string('contract_file')->nullable();
            $table->json('payment_schedule')->nullable();
            $table->date('next_payment_date')->nullable();
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
}; 