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
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('address_data');
            $table->string('street', 255)->nullable();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('address_data');
            $table->string('street', 255)->nullable();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('address_data');
            $table->string('street', 255)->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('address_data');
            $table->string('street', 255)->nullable();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('address_data');
            $table->string('street', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('street');
            $table->jsonb('address_data')->default('{}');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('street');
            $table->jsonb('address_data')->default('{}');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('street');
            $table->jsonb('address_data')->default('{}');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('street');
            $table->jsonb('address_data')->default('{}');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('street');
            $table->jsonb('address_data')->default('{}');
        });
    }
};
