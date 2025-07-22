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
            $table->enum('have_badge', ['عادي', 'ذهبي', 'ماسي'])->default('عادي')->after('type');
        });
    }

    public function down()
    {
        Schema::table('service_posts', function (Blueprint $table) {
            $table->dropColumn('have_badge');
        });
    }


};
