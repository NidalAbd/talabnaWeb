<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'apple_refresh_token')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Encrypted Apple refresh token, kept only to revoke it when the account is deleted.
            $table->text('apple_refresh_token')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('apple_refresh_token');
        });
    }
};
