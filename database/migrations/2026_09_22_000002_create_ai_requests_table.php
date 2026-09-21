<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_requests')) {
            return;
        }

        // One row per paid AI action: what was charged, what happened, and whether it was refunded.
        // The charge and this row are written in the same database transaction, so a charge without
        // a row cannot exist.
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');                          // chosen by the app; also the idempotency key
            $table->unsignedBigInteger('user_id');
            $table->string('feature', 40);
            $table->unsignedInteger('points');
            $table->string('status', 20)->default('processing'); // processing | succeeded | failed | refund_failed
            $table->unsignedBigInteger('charge_transaction_id')->nullable();
            $table->unsignedBigInteger('refund_transaction_id')->nullable();
            $table->unsignedTinyInteger('refund_attempts')->default(0);
            $table->string('provider', 20)->nullable();
            $table->string('provider_job_id', 100)->nullable();
            $table->text('prompt')->nullable();            // image/video prompts only (for abuse review)
            $table->json('result')->nullable();            // small text results
            $table->string('result_path')->nullable();     // generated file, on the private disk
            $table->string('error_code', 40)->nullable();
            $table->string('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'uuid']);
            $table->index(['status', 'created_at']);
            $table->index(['feature', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
