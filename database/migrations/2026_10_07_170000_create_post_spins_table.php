<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Release C: a 360° view of an item, made on the phone from a walk-around video (frames only are uploaded). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('post_spins')) {
            return;
        }
        Schema::create('post_spins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_post_id')->unique();
            $table->json('frames');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_spins');
    }
};
