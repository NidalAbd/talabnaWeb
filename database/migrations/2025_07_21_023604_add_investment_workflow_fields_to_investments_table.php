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
        // This migration is no longer needed as the fields are included in the main investments table
        // migration. This is kept for backward compatibility.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration is no longer needed as the fields are included in the main investments table
        // migration. This is kept for backward compatibility.
    }
};
