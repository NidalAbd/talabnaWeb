<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banned_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('device_id')->unique()->index();
            $table->string('device_name')->nullable();
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('ban_reason')->nullable();
            $table->timestamp('banned_at');
            $table->timestamp('unban_at')->nullable();
            $table->timestamps();

            // Add foreign key after creating the table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banned_devices');
    }
};
