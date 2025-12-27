<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates points_audit_logs table for comprehensive transaction audit trail
     */
    public function up(): void
    {
        Schema::create('points_audit_logs', function (Blueprint $table) {
            $table->id();

            // User who performed the action
            $table->unsignedBigInteger('user_id');

            // Action details
            $table->string('action', 50); // 'transfer_sent', 'transfer_received', 'purchase', 'refund', 'admin_grant', 'admin_deduct'
            $table->integer('amount'); // Can be negative for deductions

            // Balance tracking
            $table->integer('balance_before');
            $table->integer('balance_after');

            // Reference to related record (polymorphic)
            $table->string('reference_type', 50)->nullable(); // 'point_transactions', 'point_purchase_requests'
            $table->unsignedBigInteger('reference_id')->nullable();

            // Additional context
            $table->json('metadata')->nullable(); // Additional context data
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes for common queries
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_audit_logs');
    }
};
