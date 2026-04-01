<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 8)->unique()->nullable()->after('fcm_token');
            $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
            $table->unsignedInteger('direct_referrals_count')->default(0)->after('referred_by');
            $table->unsignedInteger('total_referrals_count')->default(0)->after('direct_referrals_count');

            $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            $table->index('referred_by');
        });

        // Generate referral codes for existing users
        $users = \DB::table('users')->whereNull('referral_code')->pluck('id');
        foreach ($users as $id) {
            $code = strtoupper(Str::random(8));
            while (\DB::table('users')->where('referral_code', $code)->exists()) {
                $code = strtoupper(Str::random(8));
            }
            \DB::table('users')->where('id', $id)->update(['referral_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['referral_code', 'referred_by', 'direct_referrals_count', 'total_referrals_count']);
        });
    }
};
