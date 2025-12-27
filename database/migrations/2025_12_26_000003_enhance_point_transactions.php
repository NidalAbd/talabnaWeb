<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enhances point_transactions table with idempotency, status tracking, and audit data
     */
    public function up(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            // Idempotency support to prevent duplicate transactions
            $table->string('idempotency_key', 64)->nullable()->unique()->after('point');

            // Transaction status tracking
            $table->string('status', 20)->default('completed')->after('idempotency_key'); // 'pending', 'completed', 'failed', 'reversed'

            // Additional metadata for audit trail
            $table->json('metadata')->nullable()->after('status');
            $table->string('ip_address', 45)->nullable()->after('metadata');
            $table->string('user_agent', 500)->nullable()->after('ip_address');

            // Add indexes for performance on common queries
            $table->index(['from_user_id', 'created_at']);
            $table->index(['to_user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['from_user_id', 'created_at']);
            $table->dropIndex(['to_user_id', 'created_at']);
            $table->dropIndex(['type', 'created_at']);

            // Drop columns
            $table->dropColumn([
                'idempotency_key',
                'status',
                'metadata',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
