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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10);
            $table->string('group', 30)->default('app'); // Reduced for index
            $table->string('key', 150); // Reduced for MySQL index compatibility (10+30+150)*4=760 bytes
            $table->text('value');
            $table->timestamps();

            // Composite unique index for locale + group + key
            $table->unique(['locale', 'group', 'key'], 'translations_unique');

            // Index for faster lookups
            $table->index(['locale', 'group']);

            // Foreign key to languages table
            $table->foreign('locale')
                  ->references('code')
                  ->on('languages')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
