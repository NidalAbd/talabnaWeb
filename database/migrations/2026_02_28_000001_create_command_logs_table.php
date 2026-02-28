<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('command_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command', 100);
            $table->string('status', 20)->default('running');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('records_affected')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['command', 'started_at']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('command_logs');
    }
};
