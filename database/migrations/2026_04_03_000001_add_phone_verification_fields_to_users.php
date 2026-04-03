<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('whatsapp_verified_at')->nullable()->after('phone_verified_at');
            $table->timestamp('country_changed_at')->nullable()->after('whatsapp_verified_at');
            $table->string('pending_phone', 20)->nullable()->after('country_changed_at');
            $table->string('pending_whatsapp', 20)->nullable()->after('pending_phone');
            $table->string('phone_otp', 6)->nullable()->after('pending_whatsapp');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_verified_at', 'whatsapp_verified_at', 'country_changed_at',
                'pending_phone', 'pending_whatsapp', 'phone_otp', 'phone_otp_expires_at',
            ]);
        });
    }
};
