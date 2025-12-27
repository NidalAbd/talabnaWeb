<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds transfer security fields to users table for PIN protection and daily limits
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Transfer PIN security
            $table->string('transfer_pin_hash', 255)->nullable()->after('password');
            $table->timestamp('transfer_pin_set_at')->nullable()->after('transfer_pin_hash');

            // Daily transfer limits tracking
            $table->integer('daily_transfer_count')->default(0)->after('transfer_pin_set_at');
            $table->integer('daily_transfer_amount')->default(0)->after('daily_transfer_count');
            $table->date('last_transfer_date')->nullable()->after('daily_transfer_amount');

            // PIN lockout mechanism
            $table->integer('failed_pin_attempts')->default(0)->after('last_transfer_date');
            $table->timestamp('pin_locked_until')->nullable()->after('failed_pin_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'transfer_pin_hash',
                'transfer_pin_set_at',
                'daily_transfer_count',
                'daily_transfer_amount',
                'last_transfer_date',
                'failed_pin_attempts',
                'pin_locked_until',
            ]);
        });
    }
};
