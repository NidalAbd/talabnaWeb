<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enhances point_purchase_requests table with idempotency, auto-approval, and package support
     */
    public function up(): void
    {
        Schema::table('point_purchase_requests', function (Blueprint $table) {
            // Idempotency support to prevent duplicate purchases
            $table->string('idempotency_key', 64)->nullable()->unique()->after('status');

            // Auto-approval tracking
            $table->boolean('auto_approved')->default(false)->after('idempotency_key');
            $table->string('approval_type', 20)->default('manual')->after('auto_approved'); // 'auto', 'manual'

            // Package support with discount tracking
            $table->unsignedBigInteger('point_package_id')->nullable()->after('approval_type');
            $table->decimal('discount_applied', 5, 2)->default(0)->after('point_package_id');

            // Add indexes for performance
            $table->index(['user_id', 'status']);
        });

        // Add foreign key if point_packages table exists
        if (Schema::hasTable('point_packages')) {
            Schema::table('point_purchase_requests', function (Blueprint $table) {
                $table->foreign('point_package_id')
                    ->references('id')
                    ->on('point_packages')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_purchase_requests', function (Blueprint $table) {
            // Drop foreign key if exists
            if (Schema::hasTable('point_packages')) {
                $table->dropForeign(['point_package_id']);
            }

            // Drop indexes
            $table->dropIndex(['user_id', 'status']);

            // Drop columns
            $table->dropColumn([
                'idempotency_key',
                'auto_approved',
                'approval_type',
                'point_package_id',
                'discount_applied',
            ]);
        });
    }
};
