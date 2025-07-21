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
        Schema::create('business_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_title');
            $table->text('expense_description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('expense_category'); // license, advertising, salary, development, office, marketing, legal, etc.
            $table->date('expense_date');
            $table->enum('payment_method', ['bank_transfer', 'check', 'cash', 'credit_card'])->default('bank_transfer');
            $table->string('vendor_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('receipt_file')->nullable();
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('investment_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('recurring')->default(false);
            $table->date('next_due_date')->nullable();
            $table->timestamps();
            
            // Add foreign key constraints after table creation
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('investment_id')->references('id')->on('investments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_expenses');
    }
}; 