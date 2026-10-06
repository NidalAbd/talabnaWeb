<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Release C: price offers in a chat about a listing (offer, counter, accept, decline). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('offers')) {
            return;
        }
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->unsignedBigInteger('service_post_id')->index();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('from_user_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->nullable();
            $table->string('status', 12)->default('pending'); // pending accepted declined countered withdrawn
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
