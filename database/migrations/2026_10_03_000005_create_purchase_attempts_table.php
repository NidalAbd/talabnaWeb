<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every store purchase attempt (2026-10-03): logged when the user taps Buy,
 * before Google Play / App Store opens, then resolved to completed /
 * cancelled / failed / pending — or marked abandoned when nothing came back.
 * Shows the admin who tried to buy and where buying breaks.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_attempts')) return;
        Schema::create('purchase_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('product_id', 64);
            $table->string('platform', 16)->default('android');      // android | ios
            $table->string('status', 16)->default('attempted')->index(); // attempted|pending|completed|cancelled|failed|abandoned
            $table->string('error_code', 64)->nullable();
            $table->string('error_message', 500)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 8)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->unsignedBigInteger('purchase_request_id')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'product_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_attempts');
    }
};
