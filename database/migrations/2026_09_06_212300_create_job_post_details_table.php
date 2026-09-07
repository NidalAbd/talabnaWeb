<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Extends a service_post with job-specific fields, kept in a separate
     * table (rather than adding nullable columns to the generic
     * service_posts table) so every other category's schema stays
     * untouched. Only rows for job-category posts get a row here.
     */
    public function up(): void
    {
        Schema::create('job_post_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_post_id')->unique();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'remote'])
                ->default('full_time');
            $table->enum('experience_level', ['entry', 'mid', 'senior'])
                ->default('entry');
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->string('salary_currency', 10)->nullable();
            $table->json('required_skills')->nullable();
            $table->string('education_level')->nullable();
            $table->timestamps();

            $table->foreign('service_post_id')
                ->references('id')->on('service_posts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_details');
    }
};
