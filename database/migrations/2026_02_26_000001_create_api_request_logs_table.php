<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->index();
            $table->string('method', 10);
            $table->string('endpoint', 500)->index();
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->unsignedInteger('response_time_ms')->default(0);
            $table->string('user_agent', 500)->nullable();
            $table->string('device_id', 255)->nullable()->index();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_request_logs');
    }
};
