<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('point_purchase_requests', 'apple_transaction_id')) {
            return;
        }

        Schema::table('point_purchase_requests', function (Blueprint $table) {
            // Apple's transaction id is the idempotency key: one App Store purchase credits once.
            $table->string('apple_transaction_id')->nullable()->unique();
            $table->string('apple_product_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('point_purchase_requests', function (Blueprint $table) {
            $table->dropUnique(['apple_transaction_id']);
            $table->dropColumn(['apple_transaction_id', 'apple_product_id']);
        });
    }
};
