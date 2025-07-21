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
        Schema::create('investment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->onDelete('cascade');
            $table->decimal('payment_amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_type', ['return', 'dividend', 'interest'])->default('return');
            $table->enum('payment_method', ['bank_transfer', 'check', 'cash'])->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['completed', 'pending', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_payments');
    }
}; 