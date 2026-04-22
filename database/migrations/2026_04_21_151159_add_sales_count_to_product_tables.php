<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('sales_count')->default(0)->after('average_rating');
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('sales_count')->default(0)->after('average_rating');
        });
        Schema::table('product_cards', function (Blueprint $table) {
            $table->unsignedInteger('sales_count')->default(0)->after('average_rating');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sales_count');
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('sales_count');
        });
        Schema::table('product_cards', function (Blueprint $table) {
            $table->dropColumn('sales_count');
        });
    }
};
