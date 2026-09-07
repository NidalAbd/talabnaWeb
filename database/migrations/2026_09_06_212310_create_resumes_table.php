<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One resume per user (v1 — no versioning). Matched against
     * job_post_details rows by JobMatchingService.
     */
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('headline')->nullable();
            $table->text('summary')->nullable();
            $table->json('skills')->nullable();
            $table->unsignedInteger('experience_years')->default(0);
            $table->enum('experience_level', ['entry', 'mid', 'senior'])
                ->default('entry');
            $table->string('education_level')->nullable();
            $table->enum('desired_employment_type', ['full_time', 'part_time', 'contract', 'remote'])
                ->nullable();
            $table->unsignedInteger('desired_salary_min')->nullable();
            $table->unsignedInteger('desired_salary_max')->nullable();
            $table->unsignedBigInteger('desired_sub_categories_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('desired_sub_categories_id')
                ->references('id')->on('sub_categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
