<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('investor_name');
            $table->string('investor_email')->nullable();
            $table->decimal('investment_amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('investment_type', ['equity', 'loan', 'grant']);
            $table->date('investment_date');
            $table->decimal('expected_roi', 8, 2)->nullable();
            $table->integer('investment_period')->nullable();
            $table->decimal('investor_share', 5, 2)->default(55.00);
            $table->decimal('owner_share', 5, 2)->default(45.00);
            $table->enum('agreement_terms', ['standard', 'custom', 'equity'])->default('standard');
            $table->enum('status', ['active', 'completed', 'pending', 'profitable'])->default('active');
            $table->string('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->string('contract_file')->nullable();
            $table->json('payment_schedule')->nullable();
            $table->date('next_payment_date')->nullable();
            $table->decimal('total_paid', 12, 2)->default(0.00);
            $table->decimal('remaining_amount', 12, 2);
            $table->decimal('profit_generated', 12, 2)->default(0.00);
            $table->decimal('profit_distributed', 12, 2)->default(0.00);
            $table->decimal('profit_remaining', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('investments');
    }
};
