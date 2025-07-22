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
        if (!Schema::hasColumn('service_posts', 'level_id')) {
            Schema::table('service_posts', function (Blueprint $table) {
                $table->unsignedBigInteger('level_id')->nullable()->after('have_badge');
                $table->foreign('level_id')->references('id')->on('levels')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_posts', function (Blueprint $table) {
            //
        });
    }
};
