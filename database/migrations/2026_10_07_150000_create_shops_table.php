<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Release C: a shop page for Business users (or bought with points): logo, name, about, opening hours. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shops')) {
            return;
        }
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('name', 80);
            $table->string('logo')->nullable();
            $table->text('about')->nullable();
            $table->string('hours', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
