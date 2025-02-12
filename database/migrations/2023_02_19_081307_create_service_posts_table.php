<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('country_id')->nullable(); // Add country_id column
            $table->unsignedBigInteger('city_id')->nullable(); // Add country_id column
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('category', ['وظائف', 'اجهزة', 'عقارات', 'سيارات', 'خدمات','اخبار']);
            $table->string('sub_category')->nullable();
            $table->integer('price')->default(0);
            $table->enum('price_currency', [
                'دولار امريكي',
                'دينار اردني',
                'جنيه مصري',
                'شيكل',
            ])->default('دولار امريكي');
            $table->decimal('location_latitudes',15,8)->nullable();
            $table->decimal('location_longitudes',15,8)->nullable();
            $table->enum('type', ['عرض', 'طلب']);
            $table->enum('have_badge', ['عادي', 'ذهبي', 'ماسي'])->default('عادي');
            $table->integer('badge_duration')->default(0);
            $table->unsignedInteger('favorites_count')->default(0);
            $table->unsignedInteger('report_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->enum('state', ['published', 'archive', 'not published','rejected'])->default('published');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_posts');
    }
};
