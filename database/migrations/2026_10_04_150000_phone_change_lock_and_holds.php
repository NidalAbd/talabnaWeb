<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Verified phone / WhatsApp numbers (2026-10-04):
 * - phone_changed_at / whatsapp_changed_at: a verified number can be replaced once every 30 days.
 * - no_whatsapp: the user said they don't use WhatsApp (profile is complete without it).
 * - phone_number_holds: a number that was replaced (30 days) or whose account was deleted
 *   (90 days) can't be claimed by another account until hold_until.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone_changed_at')) {
                $table->timestamp('phone_changed_at')->nullable()->after('whatsapp_verified_at');
            }
            if (! Schema::hasColumn('users', 'whatsapp_changed_at')) {
                $table->timestamp('whatsapp_changed_at')->nullable()->after('phone_changed_at');
            }
            if (! Schema::hasColumn('users', 'no_whatsapp')) {
                $table->boolean('no_whatsapp')->default(false)->after('whatsapp_changed_at');
            }
        });

        if (! Schema::hasTable('phone_number_holds')) {
            Schema::create('phone_number_holds', function (Blueprint $table) {
                $table->id();
                $table->string('phone', 20)->index();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('reason', 20); // changed | deleted
                $table->timestamp('hold_until')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_number_holds');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_changed_at', 'whatsapp_changed_at', 'no_whatsapp']);
        });
    }
};
