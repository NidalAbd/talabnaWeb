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
        Schema::table('service_posts', function (Blueprint $table) {
            $table->dropColumn('level_id');
        });
    }

    public function down()
    {
        Schema::table('service_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('level_id')->nullable(); // Adjust type and nullability as needed
        });
    }
};
