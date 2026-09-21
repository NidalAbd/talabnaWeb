<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'sign_up_method')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('sign_up_method', 16)->nullable();
                $table->string('sign_up_platform', 16)->nullable();
                $table->string('last_login_method', 16)->nullable();
                $table->string('last_login_platform', 16)->nullable();
                $table->timestamp('last_login_at')->nullable();
            });
        }

        if (! Schema::hasTable('auth_events')) {
            Schema::create('auth_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('method', 16);
                $table->string('platform', 16);
                $table->boolean('is_signup')->default(false);
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        // Existing accounts: we know how they can sign in, not from what device.
        DB::table('users')->whereNull('sign_up_method')->whereNotNull('google_id')->update(['sign_up_method' => 'google']);
        DB::table('users')->whereNull('sign_up_method')->whereNotNull('apple_id')->update(['sign_up_method' => 'apple']);
        DB::table('users')->whereNull('sign_up_method')->update(['sign_up_method' => 'email']);
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_events');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sign_up_method', 'sign_up_platform', 'last_login_method', 'last_login_platform', 'last_login_at']);
        });
    }
};
