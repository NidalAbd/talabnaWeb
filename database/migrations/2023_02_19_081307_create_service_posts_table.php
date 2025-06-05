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
            $table->foreignId('user_id')->default(0)->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('country_id')->nullable(); // Add country_id column
            $table->unsignedBigInteger('city_id')->nullable(); // Add country_id column
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('price')->default(0);
            $table->string('price_currency_code')->nullable();
            $table->json('price_currency_name')->nullable();
            $table->decimal('location_latitudes',15,8)->nullable();
            $table->decimal('location_longitudes',15,8)->nullable();
            $table->enum('type', ['عرض', 'طلب']);
            $table->enum('have_badge', ['عادي', 'ذهبي', 'ماسي'])->default('عادي');
            $table->integer('badge_duration')->default(0);
            $table->timestamp('badge_expires_at')->nullable();
            $table->unsignedInteger('favorites_count')->default(0);
            $table->unsignedInteger('report_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->enum('state', ['published', 'archive', 'not published','rejected'])->default('published');
            $table->foreignId('categories_id')->constrained()->onDelete('cascade');
            $table->foreignId('sub_categories_id')->constrained()->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
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
