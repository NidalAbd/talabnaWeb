<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_purchase_requests', function (Blueprint $table) {
            $table->string('google_order_id')->nullable()->unique()->after('client_secret');
            $table->string('google_product_id')->nullable()->after('google_order_id');
            $table->text('google_purchase_token')->nullable()->after('google_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('point_purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['google_order_id', 'google_product_id', 'google_purchase_token']);
        });
    }
};
