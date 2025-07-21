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
        Schema::create('business_revenues', function (Blueprint $table) {
            $table->id();
            $table->string('revenue_title');
            $table->text('revenue_description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('revenue_type', ['point_sales', 'advertising', 'premium_features', 'other'])->default('point_sales');
            $table->date('revenue_date');
            $table->enum('payment_method', ['bank_transfer', 'check', 'cash', 'credit_card', 'online'])->default('online');
            $table->string('customer_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->enum('status', ['received', 'pending', 'failed'])->default('received');
            $table->foreignId('point_package_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_revenues');
    }
}; 