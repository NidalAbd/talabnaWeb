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
        Schema::create('seo_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // sync, analyze, optimize, report
            $table->string('source')->default('google_search_console'); // google_search_console, google_trends, manual
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->json('details')->nullable(); // Action details and results
            $table->text('error_message')->nullable();
            $table->integer('records_processed')->default(0);
            $table->integer('records_created')->default(0);
            $table->integer('records_updated')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();

            $table->index('action');
            $table->index('status');
            $table->index('source');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_logs');
    }
};
