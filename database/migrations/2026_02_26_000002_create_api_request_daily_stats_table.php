<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_request_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('endpoint', 500);
            $table->string('method', 10);
            $table->unsignedBigInteger('request_count')->default(0);
            $table->unsignedBigInteger('unique_users')->default(0);
            $table->unsignedBigInteger('unique_ips')->default(0);
            $table->unsignedBigInteger('error_count')->default(0);
            $table->unsignedInteger('avg_response_ms')->default(0);
            $table->unsignedInteger('max_response_ms')->default(0);
            $table->timestamps();

            $table->unique(['date', 'endpoint', 'method']);
        });

        Schema::create('api_request_daily_user_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedBigInteger('request_count')->default(0);
            $table->unsignedBigInteger('error_count')->default(0);
            $table->unsignedInteger('avg_response_ms')->default(0);
            $table->timestamps();

            $table->unique(['date', 'user_id', 'ip_address']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_request_daily_user_stats');
        Schema::dropIfExists('api_request_daily_stats');
    }
};
