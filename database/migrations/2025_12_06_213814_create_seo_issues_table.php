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
        Schema::create('seo_issues', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('issue_type'); // indexing, mobile_usability, structured_data, page_experience, etc.
            $table->enum('severity', ['critical', 'warning', 'info'])->default('warning');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('details')->nullable(); // Additional issue details
            $table->enum('status', ['open', 'in_progress', 'resolved', 'ignored'])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('url');
            $table->index('issue_type');
            $table->index('severity');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_issues');
    }
};
