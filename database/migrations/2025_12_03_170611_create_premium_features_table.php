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
        if (!Schema::hasTable('premium_features')) {
            Schema::create('premium_features', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('point_cost')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('feature_type')->default('post_enhancement'); // post_enhancement, user_benefit, system_feature
                $table->string('icon')->nullable();
                $table->string('color', 7)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_features');
    }
};
